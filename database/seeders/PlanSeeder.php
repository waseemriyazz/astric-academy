<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $coursePlans = [
            'Digital Marketing' => [
                'description' => 'Learn the fundamentals of promoting businesses online through SEO, social media, ads, and content marketing.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 99, 'duration' => '1 Month',
                        'description' => 'Learn the fundamentals of promoting businesses online through SEO, social media, ads, and content marketing.',
                        'features' => ['Practical Marketing Strategies', 'Real-world Marketing Tools', 'Job-Ready Skills'],
                        'includes' => ['SEO Fundamentals', 'Social Media Basics', 'Content Marketing Basics'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 199, 'duration' => '2 Months',
                        'description' => 'Build practical digital marketing skills with stronger campaign strategies, audience targeting, and performance tracking.',
                        'features' => ['Advanced Marketing Strategies', 'Campaign Planning Skills', 'Performance Tracking'],
                        'includes' => ['SEO & Keyword Research', 'Social Media Marketing', 'Paid Advertising Basics'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 349, 'duration' => '3 Months',
                        'description' => 'Develop advanced skills to plan, execute, and optimize digital marketing campaigns across multiple online channels.',
                        'features' => ['Advanced Campaign Management', 'Data-Driven Marketing', 'Conversion Optimization'],
                        'includes' => ['Advanced SEO', 'Google & Social Ads', 'Marketing Analytics'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 499, 'duration' => '4 Months',
                        'description' => 'Master complete digital marketing with professional strategies, real-world campaigns, analytics, and growth-focused techniques.',
                        'features' => ['Professional Marketing Skills', 'Real-world Projects', 'Growth Strategy'],
                        'includes' => ['Complete SEO Strategy', 'Multi-Channel Campaigns', 'Advanced Analytics & Optimization'],
                    ],
                ],
            ],

            'AI & Chatbot Development' => [
                'description' => 'Build and deploy intelligent AI-powered chatbots designed to automate conversations, improve customer experiences, and support real-world business operations.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 1500, 'duration' => '1 Month',
                        'features' => ['AI Fundamentals', 'Chatbot Architecture', 'Prompt Engineering'],
                        'includes' => ['AI Chatbot Setup', 'Basic Conversation Flows', 'AI Tool Integration'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 2000, 'duration' => '2 Months',
                        'features' => ['Custom AI Chatbots', 'Workflow Automation', 'API Integration'],
                        'includes' => ['Advanced Prompt Engineering', 'Custom Chatbot Development', 'Third-Party API Integration'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 3000, 'duration' => '3 Months',
                        'features' => ['Advanced AI Systems', 'Intelligent Automation', 'Business Integration'],
                        'includes' => ['AI-Powered Chatbots', 'Advanced API Integrations', 'Automated Business Workflows'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 4000, 'duration' => '4 Months',
                        'features' => ['Advanced AI Solutions', 'AI Agent Development', 'Production-Ready Systems'],
                        'includes' => ['Custom AI Agents', 'Multi-Platform Chatbots', 'Advanced AI Automation'],
                    ],
                ],
            ],

            'AI Ethics & Applications' => [
                'description' => 'Learn how to use AI responsibly while understanding ethical principles, privacy, bias, transparency, and practical AI applications across modern industries.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 1000, 'duration' => '1 Month',
                        'features' => ['AI Ethics Fundamentals', 'Responsible AI Practices', 'Practical AI Applications'],
                        'includes' => ['AI Ethics & Principles', 'Privacy & Data Protection', 'AI Use-Case Analysis'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 1750, 'duration' => '2 Months',
                        'features' => ['Ethical AI Frameworks', 'Bias & Fairness', 'AI Risk Awareness'],
                        'includes' => ['Bias Detection Concepts', 'Responsible AI Workflows', 'Real-World Case Studies'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 2500, 'duration' => '3 Months',
                        'features' => ['AI Governance', 'Risk Management', 'Ethical AI Strategy'],
                        'includes' => ['AI Governance Models', 'Risk & Impact Assessment', 'Responsible AI Implementation'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 3500, 'duration' => '4 Months',
                        'features' => ['Advanced AI Governance', 'Enterprise AI Ethics', 'Responsible AI Leadership'],
                        'includes' => ['AI Policy Development', 'Enterprise Risk Frameworks', 'Advanced AI Ethics Projects'],
                    ],
                ],
            ],

            'AI for Business Analytics' => [
                'description' => 'Learn how to use AI and data analytics to uncover business insights, automate reporting, identify trends, and support smarter data-driven decisions.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 1000, 'duration' => '1 Month',
                        'features' => ['Analytics Fundamentals', 'AI-Powered Insights', 'Business Data Basics'],
                        'includes' => ['Data Analysis Fundamentals', 'AI Analytics Tools', 'Basic Business Reporting'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 1750, 'duration' => '2 Months',
                        'features' => ['Advanced Data Analysis', 'AI-Assisted Reporting', 'Business Intelligence'],
                        'includes' => ['Data Visualization', 'AI-Powered Reports', 'Business KPI Analysis'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 2500, 'duration' => '3 Months',
                        'features' => ['Predictive Analytics', 'Automated Insights', 'Advanced Business Intelligence'],
                        'includes' => ['Predictive Data Models', 'AI Forecasting', 'Automated Analytics Workflows'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 3500, 'duration' => '4 Months',
                        'features' => ['Advanced AI Analytics', 'Strategic Forecasting', 'Enterprise Insights'],
                        'includes' => ['Advanced Predictive Analytics', 'AI-Driven Business Strategy', 'Real-World Analytics Projects'],
                    ],
                ],
            ],

            'AI for Office Productivity' => [
                'description' => 'Learn how to use AI tools to work faster, automate repetitive tasks, improve communication, and streamline everyday office workflows.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 800, 'duration' => '1 Month',
                        'features' => ['AI Productivity Basics', 'Smart Office Tools', 'Task Automation'],
                        'includes' => ['AI Writing Assistance', 'Document & Email Automation', 'AI Productivity Workflows'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 1300, 'duration' => '2 Months',
                        'features' => ['Advanced AI Tools', 'Workflow Optimization', 'Business Automation'],
                        'includes' => ['AI-Powered Documents', 'Spreadsheet Assistance', 'Meeting & Email Automation'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 1900, 'duration' => '3 Months',
                        'features' => ['Advanced Automation', 'AI Workflow Design', 'Productivity Optimization'],
                        'includes' => ['Custom AI Workflows', 'Advanced Office Automation', 'AI-Powered Reporting'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 2500, 'duration' => '4 Months',
                        'features' => ['Enterprise AI Productivity', 'Advanced Automation Systems', 'AI Workflow Strategy'],
                        'includes' => ['End-to-End AI Automation', 'Custom Productivity Systems', 'Advanced AI Implementation'],
                    ],
                ],
            ],

            'AI in Data Visualization' => [
                'description' => 'Learn how to transform complex data into clear, interactive, and insightful visualizations using AI-powered analytics and visualization tools.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 1000, 'duration' => '1 Month',
                        'features' => ['Visualization Fundamentals', 'AI Data Insights', 'Basic Dashboard Skills'],
                        'includes' => ['Data Visualization Basics', 'AI-Assisted Charts', 'Basic Dashboard Creation'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 1600, 'duration' => '2 Months',
                        'features' => ['Interactive Visualizations', 'AI-Powered Analysis', 'Dashboard Development'],
                        'includes' => ['Interactive Dashboards', 'Data Storytelling', 'AI Data Analysis'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 2300, 'duration' => '3 Months',
                        'features' => ['Advanced Data Visualization', 'Automated Insights', 'Business Intelligence'],
                        'includes' => ['Advanced Dashboards', 'AI-Powered Reporting', 'Interactive Data Exploration'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 3000, 'duration' => '4 Months',
                        'features' => ['Advanced Visualization Systems', 'AI Analytics Strategy', 'Professional Dashboard Design'],
                        'includes' => ['Enterprise Dashboards', 'Advanced AI Analytics', 'Real-World Visualization Projects'],
                    ],
                ],
            ],

            'AI in Marketing & Sales' => [
                'description' => 'Learn how to use AI to improve marketing campaigns, generate content, understand customers, automate sales processes, and drive business growth.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 1000, 'duration' => '1 Month',
                        'features' => ['AI Marketing Basics', 'Content Generation', 'Sales Automation'],
                        'includes' => ['AI Content Creation', 'Customer Research', 'Basic Sales Workflows'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 1750, 'duration' => '2 Months',
                        'features' => ['AI Campaign Strategy', 'Customer Insights', 'Marketing Automation'],
                        'includes' => ['AI-Powered Campaigns', 'Lead Generation Tools', 'Customer Segmentation'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 2500, 'duration' => '3 Months',
                        'features' => ['Advanced AI Marketing', 'Sales Optimization', 'Predictive Insights'],
                        'includes' => ['AI Campaign Optimization', 'Automated Sales Funnels', 'Predictive Customer Analytics'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 3500, 'duration' => '4 Months',
                        'features' => ['AI Growth Strategy', 'Enterprise Automation', 'Advanced Sales Intelligence'],
                        'includes' => ['End-to-End AI Marketing', 'Advanced Sales Automation', 'AI-Driven Growth Projects'],
                    ],
                ],
            ],

            'AI Tools for Professionals' => [
                'description' => 'Learn how to use modern AI tools to improve productivity, automate tasks, create content, analyze information, and work more efficiently.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 800, 'duration' => '1 Month',
                        'features' => ['AI Productivity Tools', 'Smart Workflows', 'Task Automation'],
                        'includes' => ['AI Tool Fundamentals', 'Prompt Engineering Basics', 'Everyday AI Applications'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 1300, 'duration' => '2 Months',
                        'features' => ['Advanced AI Tools', 'Workflow Optimization', 'AI-Assisted Tasks'],
                        'includes' => ['Professional AI Tools', 'Content & Research Tools', 'AI Workflow Creation'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 1900, 'duration' => '3 Months',
                        'features' => ['Advanced AI Workflows', 'Business Automation', 'Productivity Optimization'],
                        'includes' => ['Advanced AI Applications', 'Custom Workflows', 'AI-Powered Productivity Systems'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 2500, 'duration' => '4 Months',
                        'features' => ['Professional AI Strategy', 'Advanced Automation', 'Enterprise AI Tools'],
                        'includes' => ['Advanced AI Toolkits', 'End-to-End AI Workflows', 'Real-World AI Projects'],
                    ],
                ],
            ],

            'Generative AI for Content' => [
                'description' => 'Learn to create high-quality text, images, videos, and other digital content using modern generative AI tools and creative workflows.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 1000, 'duration' => '1 Month',
                        'features' => ['Generative AI Basics', 'AI Content Creation', 'Prompt Engineering'],
                        'includes' => ['AI Writing Tools', 'Basic Image Generation', 'Content Prompting'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 1600, 'duration' => '2 Months',
                        'features' => ['Advanced Content Creation', 'Creative AI Tools', 'Content Workflows'],
                        'includes' => ['AI Copywriting', 'AI Image Creation', 'Content Automation'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 2300, 'duration' => '3 Months',
                        'features' => ['Multi-Format Content', 'Advanced Prompting', 'AI Content Strategy'],
                        'includes' => ['AI Video Tools', 'Advanced Image Generation', 'Automated Content Workflows'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 3000, 'duration' => '4 Months',
                        'features' => ['Advanced Generative AI', 'Creative Automation', 'Professional Content Strategy'],
                        'includes' => ['Text, Image & Video AI', 'Advanced AI Workflows', 'Real-World Content Projects'],
                    ],
                ],
            ],

            'Intro to Artificial Intelligence' => [
                'description' => 'Understand the fundamentals of artificial intelligence, machine learning, generative AI, and how AI is being applied across modern industries.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 800, 'duration' => '1 Month',
                        'features' => ['AI Fundamentals', 'Machine Learning Basics', 'AI Applications'],
                        'includes' => ['Introduction to AI', 'AI Terminology', 'Real-World AI Examples'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 1300, 'duration' => '2 Months',
                        'features' => ['AI Concepts', 'Machine Learning Basics', 'Generative AI'],
                        'includes' => ['AI Models & Systems', 'Machine Learning Concepts', 'Generative AI Tools'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 1900, 'duration' => '3 Months',
                        'features' => ['Advanced AI Concepts', 'AI Applications', 'Practical AI Skills'],
                        'includes' => ['AI Project Development', 'Model Fundamentals', 'AI Use-Case Analysis'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 2500, 'duration' => '4 Months',
                        'features' => ['Advanced AI Knowledge', 'AI Strategy', 'Real-World Projects'],
                        'includes' => ['Advanced AI Applications', 'AI Project Work', 'Industry Use Cases'],
                    ],
                ],
            ],

            'RPA with AI' => [
                'description' => 'Learn how to combine robotic process automation with AI to automate repetitive business tasks, workflows, data processing, and operational processes.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 1500, 'duration' => '1 Month',
                        'features' => ['RPA Fundamentals', 'Process Automation', 'AI Introduction'],
                        'includes' => ['RPA Concepts', 'Basic Automation', 'AI-Powered Workflows'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 2100, 'duration' => '2 Months',
                        'features' => ['RPA Development', 'AI Integration', 'Workflow Automation'],
                        'includes' => ['Automated Business Tasks', 'AI Tool Integration', 'RPA Workflow Design'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 3000, 'duration' => '3 Months',
                        'features' => ['Intelligent Automation', 'Advanced RPA', 'Business Process Optimization'],
                        'includes' => ['AI-Driven Automation', 'Advanced Workflow Design', 'Business Process Automation'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 4000, 'duration' => '4 Months',
                        'features' => ['Enterprise Automation', 'Intelligent RPA Systems', 'Advanced AI Integration'],
                        'includes' => ['End-to-End Automation', 'Advanced AI-RPA Solutions', 'Real-World Automation Projects'],
                    ],
                ],
            ],

            'Advanced Excel Course' => [
                'description' => 'Master advanced Excel techniques for data management, complex calculations, reporting, analysis, and professional business workflows.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 300, 'duration' => '1 Month',
                        'features' => ['Advanced Excel Basics', 'Formula Mastery', 'Data Management'],
                        'includes' => ['Advanced Formulas', 'Data Formatting', 'Excel Functions'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 500, 'duration' => '2 Months',
                        'features' => ['Data Analysis', 'Advanced Functions', 'Professional Reports'],
                        'includes' => ['Pivot Tables', 'Lookup Functions', 'Advanced Data Analysis'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 750, 'duration' => '3 Months',
                        'features' => ['Advanced Analytics', 'Dashboard Creation', 'Data Automation'],
                        'includes' => ['Interactive Dashboards', 'Advanced Pivot Tables', 'Data Analysis Workflows'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 1000, 'duration' => '4 Months',
                        'features' => ['Excel Mastery', 'Advanced Reporting', 'Business Intelligence'],
                        'includes' => ['Advanced Excel Projects', 'Automated Reports', 'Professional Dashboards'],
                    ],
                ],
            ],

            'Automation VBA & Macros' => [
                'description' => 'Learn how to automate repetitive Excel tasks using VBA and macros, saving time and improving business productivity.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 800, 'duration' => '1 Month',
                        'features' => ['VBA Fundamentals', 'Macro Basics', 'Task Automation'],
                        'includes' => ['VBA Introduction', 'Macro Recording', 'Basic Automation'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 1300, 'duration' => '2 Months',
                        'features' => ['VBA Programming', 'Custom Macros', 'Workflow Automation'],
                        'includes' => ['VBA Functions', 'Custom Macro Creation', 'Automated Excel Tasks'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 1900, 'duration' => '3 Months',
                        'features' => ['Advanced VBA', 'Process Automation', 'Custom Solutions'],
                        'includes' => ['Advanced VBA Coding', 'Automated Workflows', 'Custom Excel Tools'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 2500, 'duration' => '4 Months',
                        'features' => ['VBA Mastery', 'Enterprise Automation', 'Advanced Solutions'],
                        'includes' => ['Complex VBA Projects', 'End-to-End Automation', 'Professional Automation Systems'],
                    ],
                ],
            ],

            'Basic Excel Course' => [
                'description' => 'Learn the essential Excel skills needed to create spreadsheets, manage data, use formulas, and work confidently with everyday business tasks.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 100, 'duration' => '1 Month',
                        'features' => ['Excel Fundamentals', 'Spreadsheet Basics', 'Essential Formulas'],
                        'includes' => ['Excel Interface', 'Basic Formatting', 'Simple Formulas'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 200, 'duration' => '2 Months',
                        'features' => ['Formula Skills', 'Data Management', 'Spreadsheet Organization'],
                        'includes' => ['Excel Functions', 'Data Sorting & Filtering', 'Charts & Tables'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 350, 'duration' => '3 Months',
                        'features' => ['Advanced Formulas', 'Data Analysis', 'Professional Reports'],
                        'includes' => ['Lookup Functions', 'Pivot Tables', 'Advanced Charts'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 500, 'duration' => '4 Months',
                        'features' => ['Complete Excel Skills', 'Advanced Analysis', 'Professional Workflows'],
                        'includes' => ['Advanced Excel Projects', 'Data Reporting', 'Practical Business Tasks'],
                    ],
                ],
            ],

            'Data Analysis with Excel' => [
                'description' => 'Learn how to analyze, clean, organize, and interpret data using Excel to generate meaningful insights and support better business decisions.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 300, 'duration' => '1 Month',
                        'features' => ['Data Analysis Basics', 'Data Cleaning', 'Excel Functions'],
                        'includes' => ['Data Organization', 'Basic Formulas', 'Sorting & Filtering'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 550, 'duration' => '2 Months',
                        'features' => ['Advanced Analysis', 'Pivot Tables', 'Data Insights'],
                        'includes' => ['Data Cleaning Techniques', 'Pivot Table Analysis', 'Lookup Functions'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 850, 'duration' => '3 Months',
                        'features' => ['Advanced Analytics', 'Business Reporting', 'Data Modeling'],
                        'includes' => ['Advanced Pivot Tables', 'Interactive Reports', 'Analytical Dashboards'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 1200, 'duration' => '4 Months',
                        'features' => ['Professional Data Analysis', 'Advanced Reporting', 'Business Intelligence'],
                        'includes' => ['End-to-End Data Projects', 'Advanced Excel Analytics', 'Business Decision Models'],
                    ],
                ],
            ],

            'Data Visualization with Excel' => [
                'description' => 'Learn how to transform complex datasets into clear, interactive, and professional Excel charts, dashboards, and visual reports.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 800, 'duration' => '1 Month',
                        'features' => ['Visualization Basics', 'Excel Charts', 'Data Presentation'],
                        'includes' => ['Basic Charts', 'Chart Formatting', 'Data Presentation'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 1200, 'duration' => '2 Months',
                        'features' => ['Interactive Charts', 'Dashboard Basics', 'Data Storytelling'],
                        'includes' => ['Advanced Charts', 'Interactive Reports', 'Dashboard Elements'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 1600, 'duration' => '3 Months',
                        'features' => ['Advanced Dashboards', 'Data Storytelling', 'Interactive Reporting'],
                        'includes' => ['Dynamic Dashboards', 'Advanced Visualizations', 'KPI Reporting'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 2000, 'duration' => '4 Months',
                        'features' => ['Professional Dashboards', 'Advanced Visualization', 'Business Intelligence'],
                        'includes' => ['Executive Dashboards', 'Advanced Excel Reports', 'Real-World Visualization Projects'],
                    ],
                ],
            ],

            'Website Development' => [
                'description' => 'Learn to plan, design, develop, and launch modern websites using professional development techniques, tools, and real-world projects.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 2000, 'duration' => '1 Month',
                        'features' => ['Web Development Basics', 'Website Structure', 'Front-End Fundamentals'],
                        'includes' => ['HTML & CSS', 'Basic JavaScript', 'Website Setup'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 3000, 'duration' => '2 Months',
                        'features' => ['Responsive Development', 'Dynamic Websites', 'Web Design Skills'],
                        'includes' => ['JavaScript Development', 'Responsive Design', 'CMS Fundamentals'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 4000, 'duration' => '3 Months',
                        'features' => ['Full-Stack Development', 'Database Integration', 'Advanced Web Solutions'],
                        'includes' => ['Front-End Development', 'Back-End Fundamentals', 'Database Integration'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 5000, 'duration' => '4 Months',
                        'features' => ['Full-Stack Mastery', 'Production-Ready Websites', 'Real-World Projects'],
                        'includes' => ['Complete Website Projects', 'Advanced Web Development', 'Deployment & Optimization'],
                    ],
                ],
            ],

            'Graphic Design' => [
                'description' => 'Develop professional graphic design skills for creating compelling visual identities, marketing materials, digital graphics, and brand assets.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 1500, 'duration' => '1 Month',
                        'features' => ['Design Fundamentals', 'Creative Concepts', 'Design Tools'],
                        'includes' => ['Typography Basics', 'Color Theory', 'Basic Graphic Design'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 2100, 'duration' => '2 Months',
                        'features' => ['Digital Design', 'Branding Basics', 'Visual Communication'],
                        'includes' => ['Logo Design', 'Social Media Graphics', 'Marketing Materials'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 3000, 'duration' => '3 Months',
                        'features' => ['Advanced Design', 'Brand Development', 'Professional Projects'],
                        'includes' => ['Brand Identity Design', 'Advanced Layouts', 'Digital Campaign Assets'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 4000, 'duration' => '4 Months',
                        'features' => ['Professional Design Skills', 'Complete Branding', 'Creative Direction'],
                        'includes' => ['Full Brand Projects', 'Advanced Design Systems', 'Professional Portfolio Projects'],
                    ],
                ],
            ],

            'Email Marketing' => [
                'description' => 'Learn how to create effective email campaigns, build subscriber lists, automate communication, and improve customer engagement and conversions.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 300, 'duration' => '1 Month',
                        'features' => ['Email Marketing Basics', 'Campaign Creation', 'Audience Building'],
                        'includes' => ['Email Campaign Setup', 'Basic Templates', 'Subscriber Management'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 500, 'duration' => '2 Months',
                        'features' => ['Email Automation', 'Audience Segmentation', 'Campaign Optimization'],
                        'includes' => ['Automated Email Sequences', 'List Segmentation', 'Campaign Tracking'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 750, 'duration' => '3 Months',
                        'features' => ['Advanced Campaigns', 'Conversion Optimization', 'Email Analytics'],
                        'includes' => ['Advanced Automation', 'A/B Testing', 'Performance Analytics'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 1000, 'duration' => '4 Months',
                        'features' => ['Email Marketing Strategy', 'Advanced Automation', 'Growth Optimization'],
                        'includes' => ['Complete Email Funnels', 'Advanced Campaign Strategy', 'Real-World Email Projects'],
                    ],
                ],
            ],

            'Search Engine Optimization (SEO)' => [
                'description' => 'Learn how to improve website visibility, increase organic traffic, optimize content, and build effective search engine optimization strategies.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 300, 'duration' => '1 Month',
                        'features' => ['SEO Fundamentals', 'Keyword Research', 'On-Page SEO'],
                        'includes' => ['SEO Basics', 'Keyword Research', 'Basic Website Optimization'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 550, 'duration' => '2 Months',
                        'features' => ['Technical SEO', 'Content Optimization', 'Link Building'],
                        'includes' => ['Technical SEO Basics', 'Content SEO', 'Off-Page SEO'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 850, 'duration' => '3 Months',
                        'features' => ['Advanced SEO', 'SEO Analytics', 'Growth Strategy'],
                        'includes' => ['Technical Optimization', 'Competitor Analysis', 'SEO Performance Tracking'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 1200, 'duration' => '4 Months',
                        'features' => ['Complete SEO Strategy', 'Advanced Optimization', 'Organic Growth'],
                        'includes' => ['Full SEO Audits', 'Advanced SEO Campaigns', 'Real-World SEO Projects'],
                    ],
                ],
            ],

            'Social Media Marketing' => [
                'description' => 'Learn how to build social media strategies, create engaging campaigns, grow audiences, and use analytics to improve social media performance.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 300, 'duration' => '1 Month',
                        'features' => ['Social Media Basics', 'Content Planning', 'Audience Engagement'],
                        'includes' => ['Social Media Strategy', 'Content Calendar', 'Basic Analytics'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 550, 'duration' => '2 Months',
                        'features' => ['Campaign Management', 'Content Strategy', 'Audience Growth'],
                        'includes' => ['Social Media Campaigns', 'Content Creation', 'Performance Tracking'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 850, 'duration' => '3 Months',
                        'features' => ['Advanced Campaigns', 'Paid Social Marketing', 'Growth Strategy'],
                        'includes' => ['Social Media Advertising', 'Advanced Analytics', 'Influencer & Brand Strategy'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 1200, 'duration' => '4 Months',
                        'features' => ['Complete Social Strategy', 'Multi-Platform Marketing', 'Growth Optimization'],
                        'includes' => ['Full Social Media Campaigns', 'Paid & Organic Strategy', 'Real-World Marketing Projects'],
                    ],
                ],
            ],

            'Product Management' => [
                'description' => 'Learn how to manage products from idea to launch using product strategy, customer research, roadmap planning, analytics, and cross-functional leadership.',
                'tiers' => [
                    [
                        'tier_name' => 'Basic', 'price' => 2000, 'duration' => '1 Month',
                        'features' => ['Product Fundamentals', 'Market Research', 'Product Strategy'],
                        'includes' => ['Product Lifecycle', 'Customer Research', 'Basic Product Planning'],
                    ],
                    [
                        'tier_name' => 'Intermediate', 'price' => 3000, 'duration' => '2 Months',
                        'features' => ['Product Roadmapping', 'User Research', 'Agile Methods'],
                        'includes' => ['Product Roadmaps', 'User Personas', 'Agile Product Management'],
                    ],
                    [
                        'tier_name' => 'Advanced', 'price' => 4000, 'duration' => '3 Months',
                        'features' => ['Product Strategy', 'Data-Driven Decisions', 'Growth Management'],
                        'includes' => ['Product Analytics', 'Go-to-Market Strategy', 'Product Growth Planning'],
                    ],
                    [
                        'tier_name' => 'Masters', 'price' => 5000, 'duration' => '4 Months',
                        'features' => ['Product Leadership', 'Strategic Management', 'End-to-End Product Skills'],
                        'includes' => ['Complete Product Strategy', 'Product Launch Planning', 'Real-World Product Projects'],
                    ],
                ],
            ],
        ];

        $createdCount = 0;
        $skippedCourses = [];

        foreach ($coursePlans as $title => $data) {
            $course = Course::where('title', $title)->first();

            if (! $course) {
                $skippedCourses[] = $title;
                continue;
            }

            foreach ($data['tiers'] as $sortOrder => $tier) {
                Plan::updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'tier_name' => $tier['tier_name'],
                    ],
                    [
                        'slug' => Str::slug($course->title . '-' . $tier['tier_name']),
                        'description' => $tier['description'] ?? $data['description'],
                        'price' => $tier['price'],
                        'features' => $tier['features'],
                        'duration' => $tier['duration'],
                        'includes' => $tier['includes'],
                        'sort_order' => $sortOrder + 1,
                        'is_active' => true,
                    ]
                );

                $createdCount++;
            }

            // Remove stale tiers (e.g. leftover "Pro" plans from the old generic seeder)
            // that are no longer part of this course's real tier list.
            Plan::where('course_id', $course->id)
                ->whereNotIn('tier_name', collect($data['tiers'])->pluck('tier_name'))
                ->delete();
        }

        if (! empty($skippedCourses)) {
            $this->command->warn('Skipped plans for missing courses: ' . implode(', ', $skippedCourses));
        }

        $this->command->info("Seeded {$createdCount} plans across " . count($coursePlans) . ' courses.');
    }
}
