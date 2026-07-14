<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatBotController extends Controller
{
    public function sendMessage(Request $request, Course $course, Lesson $lesson)
    {
        $user = Auth::user();

        // Verify enrollment
        if (!$user->courses()->where('courses.id', $course->id)->exists()) {
            return response()->json(['error' => 'Not enrolled in this course.'], 403);
        }

        // Verify lesson belongs to course
        if ($lesson->course_id !== $course->id) {
            return response()->json(['error' => 'Lesson not found in this course.'], 404);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        // Log the student's message
        Log::info('Chat Message', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'course_id' => $course->id,
            'course_title' => $course->title,
            'lesson_id' => $lesson->id,
            'lesson_title' => $lesson->title,
            'message' => $validated['message'],
        ]);

        // Build the system prompt with course context
        $allLessons = $course->lessons()->orderBy('order')->get();
        $lessonTitles = $allLessons->pluck('title')->implode("\n");

        $systemPrompt = <<<PROMPT
You are an AI tutor for an online course. Answer the student's question based on the course content below. Be helpful, concise, and educational. If the question is outside the scope of the course, politely redirect back to the course material.

COURSE CONTEXT:
- Course Title: {$course->title}
- Course Description: {$course->description}

CURRENT LESSON:
- Title: {$lesson->title}
- Description: {$lesson->description}
- Summary: {$lesson->summary}
- Duration: {$lesson->duration}

ALL LESSONS IN THIS COURSE (in order):
{$lessonTitles}

Answer the student's question using only the context above. If you don't know the answer based on the provided context, say so honestly.
PROMPT;

        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            Log::error('Gemini API key not configured');
            return response()->json(['error' => 'AI service not configured. Please add GEMINI_API_KEY to your .env file.'], 500);
        }

        try {
            // Use gemini-3.5-flash (latest stable flash model - confirmed responding)
         $modelName = 'gemini-3.5-flash';
$url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}";

Log::info('Calling Gemini API', ['model' => $modelName, 'url' => $url]);

$response = Http::timeout(30)->post($url, [
    'contents' => [
        [
            'role' => 'user',
            'parts' => [
                ['text' => $systemPrompt . "\n\nStudent's question: " . $validated['message']]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 1024,
    ]
]);

            if ($response->failed()) {
                $errorBody = $response->body();
                Log::error('Gemini API error', [
                    'status' => $response->status(), 
                    'body' => $errorBody,
                    'model' => $modelName
                ]);
                
                // Try to parse error message
                $errorMessage = 'AI service temporarily unavailable.';
                if ($response->status() === 404) {
                    $errorMessage = "AI model '{$modelName}' not found. Please check the model name.";
                } elseif ($response->status() === 400) {
                    $errorMessage = 'Invalid request to AI service.';
                }
                
                return response()->json(['error' => $errorMessage], 500);
            }

            $data = $response->json();
            Log::info('Gemini API response', ['data' => $data]);
            
            $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            
            if (!$reply) {
                Log::warning('Empty reply from Gemini', ['data' => $data]);
                return response()->json(['error' => 'AI service returned an empty response.'], 500);
            }

            // Log the AI reply
            Log::info('Chat Reply', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'course_id' => $course->id,
                'lesson_id' => $lesson->id,
                'reply' => $reply,
            ]);

            return response()->json(['reply' => $reply]);

        } catch (\Exception $e) {
            Log::error('ChatBot error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred processing your request.'], 500);
        }
    }
}