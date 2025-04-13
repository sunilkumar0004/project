<?php
// Backend PHP Logic
session_start();


if (!isset($_SESSION['user_profile'])) {
    $_SESSION['user_profile'] = [
        'skill_level' => 'beginner',
        'interests' => [],
        'completed_courses' => [],
        'in_progress_courses' => [],
        'recommended_path' => []
    ];
}

// Simple user database simulation
$users = [
    'student@example.com' => [
        'password' => password_hash('student123', PASSWORD_DEFAULT),
        'name' => 'Sample Student',
        'role' => 'student'
    ],
    'admin@example.com' => [
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'name' => 'Admin User',
        'role' => 'admin'
    ]
];

// Sample course database with categories
$courses = [
    ['id' => 1, 'title' => 'Introduction to Programming', 'difficulty' => 'beginner', 
     'tags' => ['programming', 'basics'], 'duration' => 4, 'category' => 'programming',
     'description' => 'Learn the fundamentals of programming with simple examples and exercises.'],
     
    ['id' => 2, 'title' => 'Web Development Fundamentals', 'difficulty' => 'beginner', 
     'tags' => ['web', 'html', 'css'], 'duration' => 6, 'category' => 'web',
     'description' => 'Build your first website with HTML and CSS.'],
     
    ['id' => 3, 'title' => 'Advanced JavaScript', 'difficulty' => 'intermediate', 
     'tags' => ['javascript', 'web'], 'duration' => 8, 'category' => 'web',
     'description' => 'Master JavaScript concepts like closures, promises, and async/await.'],
     
    ['id' => 4, 'title' => 'PHP Backend Development', 'difficulty' => 'intermediate', 
     'tags' => ['php', 'backend'], 'duration' => 7, 'category' => 'web',
     'description' => 'Create dynamic web applications with PHP and MySQL.'],
     
    ['id' => 5, 'title' => 'Machine Learning Basics', 'difficulty' => 'advanced', 
     'tags' => ['ai', 'python'], 'duration' => 10, 'category' => 'data-science',
     'description' => 'Introduction to machine learning algorithms using Python.'],
     
    ['id' => 6, 'title' => 'Database Design', 'difficulty' => 'intermediate', 
     'tags' => ['sql', 'database'], 'duration' => 5, 'category' => 'data-science',
     'description' => 'Learn how to design efficient database structures.'],
     
    ['id' => 7, 'title' => 'Mobile App Development', 'difficulty' => 'intermediate', 
     'tags' => ['mobile', 'flutter'], 'duration' => 9, 'category' => 'mobile',
     'description' => 'Build cross-platform mobile apps with Flutter.'],
     
    ['id' => 8, 'title' => 'DevOps Fundamentals', 'difficulty' => 'intermediate', 
     'tags' => ['devops', 'cloud'], 'duration' => 6, 'category' => 'devops',
     'description' => 'Introduction to CI/CD pipelines and cloud deployment.']
];

// Course categories
$categories = [
    'web' => 'Web Development',
    'programming' => 'Programming',
    'data-science' => 'Data Science',
    'mobile' => 'Mobile Development',
    'devops' => 'DevOps'
];


if (!isset($_SESSION['user_profile']['completed_courses'])) {
    $_SESSION['user_profile']['completed_courses'] = [];
}
if (!isset($_SESSION['user_profile']['in_progress_courses'])) {
    $_SESSION['user_profile']['in_progress_courses'] = [];
}

// Initialize user profile if not exists
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'logged_in' => false,
        'email' => '',
        'name' => '',
        'role' => 'student'
    ];
}

if (!isset($_SESSION['user_profile'])) {
    $_SESSION['user_profile'] = [
        'skill_level' => 'beginner',
        'interests' => [],
        'completed_courses' => [],
        'in_progress_courses' => [], // Initialize as empty array
        'recommended_path' => [],
        'last_activity' => null
    ];
}


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Handle login
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (isset($users[$email])) {
            if (password_verify($password, $users[$email]['password'])) {
                $_SESSION['user']['logged_in'] = true;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['name'] = $users[$email]['name'];
                $_SESSION['user']['role'] = $users[$email]['role'];
            } else {
                $login_error = "Invalid email or password";
            }
        } else {
            $login_error = "User not found";
        }
    } elseif (isset($_POST['logout'])) {
        // Handle logout
        session_destroy();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } elseif (isset($_POST['update_profile'])) {
        // Update user profile
        $_SESSION['user_profile']['skill_level'] = $_POST['skill_level'] ?? 'beginner';
        $_SESSION['user_profile']['interests'] = $_POST['interests'] ?? [];
        
        // Generate recommendations
        generateRecommendations();
    } elseif (isset($_POST['complete_course'])) {
        // Mark course as completed
        $courseId = (int)$_POST['course_id'];
        if (!in_array($courseId, $_SESSION['user_profile']['completed_courses'])) {
            // Remove from in progress if it was there
            $_SESSION['user_profile']['in_progress_courses'] = array_diff(
                $_SESSION['user_profile']['in_progress_courses'], 
                [$courseId]
            );
            
            $_SESSION['user_profile']['completed_courses'][] = $courseId;
            $_SESSION['user_profile']['last_activity'] = date('Y-m-d H:i:s');
            generateRecommendations();
        }
    } elseif (isset($_POST['start_course'])) {
        // Start a course
        $courseId = (int)$_POST['course_id'];
        if (!in_array($courseId, $_SESSION['user_profile']['in_progress_courses']) && 
            !in_array($courseId, $_SESSION['user_profile']['completed_courses'])) {
            $_SESSION['user_profile']['in_progress_courses'][] = $courseId;
            $_SESSION['user_profile']['last_activity'] = date('Y-m-d H:i:s');
            generateRecommendations();
        }
    }
}





// Function to generate recommendations with enhanced algorithm
function generateRecommendations() {
    global $courses;


    $user_profile = $_SESSION['user_profile'];
    
    if (isset($course) && isset($score['score'])) {
        $recommendations[] = [
            'course' => $course,
            'score' => $score['score'],
            'reason' => !empty($reasons) ? implode(", ", $reasons) : 'Recommended based on our system'
        ];
    }
    
    $user_interests = $user_profile['interests'];
    $user_skill_level = $user_profile['skill_level'];
    
    foreach ($courses as $course) {
        // Skip completed courses
        if (in_array($course['id'], $user_profile['completed_courses'])) {
            continue;
        }
        
        // If already in progress, give it highest priority
        if (in_array($course['id'], $user_profile['in_progress_courses'])) {
            $recommendations[] = [
                'course' => $course,
                'score' => 100, // Highest score for in-progress courses
                'reason' => 'You already started this course'
            ];
            continue;
        }

        $is_in_progress = in_array($course['id'], $user_profile['in_progress_courses'] ?? []);
        
        $score = 0;
        $reasons = [];
        
        // Skill level matching (40% weight)
        $skill_scores = [
            'beginner' => ['beginner' => 4, 'intermediate' => 2, 'advanced' => 0],
            'intermediate' => ['beginner' => 1, 'intermediate' => 4, 'advanced' => 2],
            'advanced' => ['beginner' => 0, 'intermediate' => 2, 'advanced' => 4]
        ];
        
        $score += $skill_scores[$user_skill_level][$course['difficulty']];
        if ($skill_scores[$user_skill_level][$course['difficulty']] > 0) {
            $reasons[] = "Matches your skill level ($user_skill_level)";
        }
        
        // Interest matching (50% weight)
        $interest_match_count = 0;
        foreach ($user_interests as $interest) {
            if (in_array($interest, $course['tags'])) {
                $interest_match_count++;
            }
        }
        
        if ($interest_match_count > 0) {
            $score += $interest_match_count * 2;
            $reasons[] = "Matches ".($interest_match_count > 1 ? "$interest_match_count interests" : "1 interest");
        }
        
        // Recent activity bonus (10% weight)
        if (!empty($user_profile['last_activity'])) {
            $last_activity = strtotime($user_profile['last_activity']);
            $days_since_activity = (time() - $last_activity) / (60 * 60 * 24);
            
            if ($days_since_activity < 7) {
                // If user was active recently, recommend courses similar to last completed ones
                $last_completed = end($user_profile['completed_courses']);
                if ($last_completed) {
                    $last_course = array_filter($courses, function($c) use ($last_completed) { 
                        return $c['id'] == $last_completed; 
                    });
                    
                    if (!empty($last_course)) {
                        $last_course = array_values($last_course)[0];
                        $common_tags = array_intersect($last_course['tags'], $course['tags']);
                        if (count($common_tags) > 0) {
                            $score += 1;
                            $reasons[] = "Related to your recent activity";
                        }
                    }
                }
            }
        }
        
        if ($score > 0) {
            $recommendations[] = [
                'course' => $course,
                'score' => $score,
                'reason' => implode(", ", $reasons)
            ];
        }
    }
    
    // Sort by score
    usort($recommendations, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    // Store top 5 recommendations
    $_SESSION['user_profile']['recommended_path'] = array_slice($recommendations, 0, 5);
}



// Generate initial recommendations if empty
if (empty($_SESSION['user_profile']['recommended_path']) && $_SESSION['user']['logged_in']) {
    generateRecommendations();
}

// Calculate progress statistics
// Replace the calculateProgress() function with this:
function calculateProgress() {
    global $courses;
    $completed = isset($_SESSION['user_profile']['completed_courses']) ? 
                 count($_SESSION['user_profile']['completed_courses']) : 0;
    $in_progress = isset($_SESSION['user_profile']['in_progress_courses']) ? 
                   count($_SESSION['user_profile']['in_progress_courses']) : 0;
    $total = count($courses);
    
    return [
        'completed' => $completed,
        'in_progress' => $in_progress,
        'total' => $total,
        'completion_percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
        'in_progress_percentage' => $total > 0 ? round(($in_progress / $total) * 100) : 0
    ];
}

$progress_stats = calculateProgress();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Learning Path | <?= $_SESSION['user']['logged_in'] ? htmlspecialchars($_SESSION['user']['name']) : 'Guest' ?></title>
    <!-- Tailwind CSS CDN with dark mode -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .course-card {
            transition: all 0.3s ease;
        }
        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .difficulty-beginner {
            background-color: #dcfce7;
            color: #166534;
        }
        .difficulty-intermediate {
            background-color: #fef9c3;
            color: #854d0e;
        }
        .difficulty-advanced {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .dark .difficulty-beginner {
            background-color: #166534;
            color: #dcfce7;
        }
        .dark .difficulty-intermediate {
            background-color: #854d0e;
            color: #fef9c3;
        }
        .dark .difficulty-advanced {
            background-color: #991b1b;
            color: #fee2e2;
        }
        .progress-ring__circle {
            transition: stroke-dashoffset 0.5s ease;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen transition-colors duration-300">
    <!-- Header/Navigation -->
    <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-10">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <span class="text-xl font-bold text-primary-600">LearnPath</span>
            </div>
            
            <div class="flex items-center space-x-4">
                <button id="theme-toggle" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                    <i class="fas fa-moon dark:hidden"></i>
                    <i class="fas fa-sun hidden dark:block"></i>
                </button>
                
                <?php if ($_SESSION['user']['logged_in']): ?>
                    <div class="relative group">
                        <button class="flex items-center space-x-2 focus:outline-none">
                            <div class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                                <span class="text-primary-700 dark:text-primary-200 font-medium">
                                    <?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?>
                                </span>
                            </div>
                            <span class="hidden md:inline font-medium"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-20 hidden group-hover:block">
                            <form method="POST">
                                <button type="submit" name="logout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="#login" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition">
                        Sign In
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <?php if (!$_SESSION['user']['logged_in']): ?>
            <!-- Login/Signup Section -->
            <div id="login" class="max-w-md mx-auto bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden p-8 my-12">
                <h1 class="text-2xl font-bold text-center text-primary-600 mb-6">Welcome to LearnPath</h1>
                
                <?php if (isset($login_error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($login_error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-4">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="email">
                            Email Address
                        </label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-700">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2" for="password">
                            Password
                        </label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-700">
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <button type="submit" name="login" 
                                class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition">
                            Sign In
                        </button>
                    </div>
                    
                    <div class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
                        Demo credentials: student@example.com / student123
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Dashboard Content -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Sidebar/Profile Section -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- User Profile Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex flex-col items-center">
                            <div class="w-20 h-20 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center mb-4">
                                <span class="text-2xl text-primary-700 dark:text-primary-200 font-medium">
                                    <?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?>
                                </span>
                            </div>
                            <h2 class="text-xl font-semibold"><?= htmlspecialchars($_SESSION['user']['name']) ?></h2>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Student</p>
                        </div>
                        
                        <div class="mt-6">
                            <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Your Progress</h3>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm">Courses Completed</span>
                                <span class="text-sm font-medium"><?= $progress_stats['completed'] ?>/<?= $progress_stats['total'] ?></span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-primary-600 h-2.5 rounded-full" 
                                     style="width: <?= $progress_stats['completion_percentage'] ?>%"></div>
                            </div>
                            
                            <div class="flex justify-between items-center mt-3 mb-1">
                                <span class="text-sm">In Progress</span>
                                <span class="text-sm font-medium"><?= $progress_stats['in_progress'] ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile Settings -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-lg mb-4">Learning Preferences</h3>
                        <form method="POST">
                            <div class="mb-4">
                                <label class="block text-gray-700 dark:text-gray-300 mb-2">Skill Level</label>
                                <select name="skill_level" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-700">
                                    <option value="beginner" <?= $_SESSION['user_profile']['skill_level'] === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                                    <option value="intermediate" <?= $_SESSION['user_profile']['skill_level'] === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                                    <option value="advanced" <?= $_SESSION['user_profile']['skill_level'] === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 dark:text-gray-300 mb-2">Interests (Select up to 3)</label>
                                <div class="space-y-2">
                                    <?php 
                                    $all_interests = ['programming', 'web', 'javascript', 'php', 'database', 'ai', 'mobile', 'devops'];
                                    foreach ($all_interests as $interest): ?>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="interests[]" value="<?= $interest ?>" 
                                                   <?= in_array($interest, $_SESSION['user_profile']['interests']) ? 'checked' : '' ?> 
                                                   class="mr-2 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700">
                                            <span class="capitalize"><?= $interest ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <button type="submit" name="update_profile" class="w-full bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-md transition">
                                Update Preferences
                            </button>
                        </form>
                    </div>
                    
                    <!-- Categories Filter -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="font-semibold text-lg mb-4">Categories</h3>
                        <div class="space-y-2">
                            <a href="#" class="block px-3 py-2 rounded-md bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-200 font-medium">
                                All Categories
                            </a>
                            <?php foreach ($categories as $slug => $name): ?>
                                <a href="#" class="block px-3 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                    <?= htmlspecialchars($name) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Main Content Area -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Welcome Banner -->
                    <div class="bg-gradient-to-r from-primary-500 to-primary-700 rounded-lg shadow overflow-hidden">
                        <div class="p-6 md:p-8 text-white">
                            <h1 class="text-2xl md:text-3xl font-bold mb-2">Welcome back, <?= htmlspecialchars(explode(' ', $_SESSION['user']['name'])[0]) ?>!</h1>
                            <p class="opacity-90 mb-4">Continue your learning journey with personalized recommendations.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm"><?= $progress_stats['completed'] ?> courses completed</span>
                                <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm"><?= $progress_stats['in_progress'] ?> in progress</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recommended Path -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold">Your Recommended Path</h2>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Updated just for you</span>
                        </div>
                        
                        <?php if (empty($_SESSION['user_profile']['recommended_path'])): ?>
                            <div class="bg-blue-50 dark:bg-blue-900 dark:bg-opacity-30 text-blue-800 dark:text-blue-200 p-4 rounded-lg">
                                <p>Complete your profile to get personalized recommendations.</p>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($_SESSION['user_profile']['recommended_path'] as $index => $rec): 
                                $course = $rec['course'];
                                $is_in_progress = isset($_SESSION['user_profile']['in_progress_courses']) && 
                                is_array($_SESSION['user_profile']['in_progress_courses']) && 
                                in_array($course['id'], $_SESSION['user_profile']['in_progress_courses']);
                            ?>
                                    <div class="course-card border border-gray-200 dark:border-gray-700 rounded-lg p-5 hover:border-primary-300 dark:hover:border-primary-500">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div class="flex items-center mb-2">
                                                    <span class="text-xs px-2 py-1 rounded-full mr-2 difficulty-<?= $course['difficulty'] ?>">
                                                        <?= ucfirst($course['difficulty']) ?>
                                                    </span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        <i class="far fa-clock mr-1"></i><?= $course['duration'] ?> weeks
                                                    </span>
                                                </div>
                                                <h3 class="font-semibold text-lg mb-1"><?= htmlspecialchars($course['title']) ?></h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
                                                    <?= isset($course['description']) ? htmlspecialchars($course['description']) : 'No description available' ?>
                                                </p>
                                                <div class="flex flex-wrap gap-2">
                                                    <?php foreach ($course['tags'] as $tag): ?>
                                                        <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-full">
                                                            <?= htmlspecialchars($tag) ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <form method="POST">
                                                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                                <?php if ($is_in_progress): ?>
                                                    <button type="submit" name="complete_course" 
                                                            class="flex items-center justify-center w-9 h-9 rounded-full bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200 hover:bg-green-200 dark:hover:bg-green-800 transition">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" name="start_course" 
                                                            class="flex items-center justify-center w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-200 hover:bg-primary-200 dark:hover:bg-primary-800 transition">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        </div>
                                        <?php if ($index === 0 && isset($rec['reason'])): ?>
                                            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center">
                                                <span class="text-xs bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-200 px-2 py-1 rounded mr-2">
                                                    Top Pick
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    <?= htmlspecialchars($rec['reason']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- In Progress Courses -->
                    <?php if (!empty($_SESSION['user_profile']['in_progress_courses'])): ?>
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <h2 class="text-xl font-semibold mb-6">Continue Learning</h2>
                            <div class="space-y-4">
                                <?php foreach ($_SESSION['user_profile']['in_progress_courses'] as $courseId): 
                                    $course = array_filter($courses, function($c) use ($courseId) { return $c['id'] == $courseId; });
                                    if (!empty($course)) {
                                        $course = array_values($course)[0]; ?>
                                        <div class="course-card border border-gray-200 dark:border-gray-700 rounded-lg p-5 hover:border-primary-300 dark:hover:border-primary-500">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <div class="flex items-center mb-2">
                                                        <span class="text-xs px-2 py-1 rounded-full mr-2 difficulty-<?= $course['difficulty'] ?>">
                                                            <?= ucfirst($course['difficulty']) ?>
                                                        </span>
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                                            <i class="far fa-clock mr-1"></i><?= $course['duration'] ?> weeks
                                                        </span>
                                                        <span class="text-xs text-yellow-600 dark:text-yellow-400 ml-2">
                                                            <i class="fas fa-spinner mr-1"></i>In Progress
                                                        </span>
                                                    </div>
                                                    <h3 class="font-semibold text-lg mb-1"><?= htmlspecialchars($course['title']) ?></h3>
                                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-3"><?= htmlspecialchars($course['description']) ?></p>
                                                </div>
                                                <form method="POST">
                                                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                                    <button type="submit" name="complete_course" 
                                                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm transition">
                                                        Mark Complete
                                                    </button>
                                                </form>
                                            </div>
                                            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                                                <div class="flex justify-between items-center text-sm mb-1">
                                                    <span class="text-gray-500 dark:text-gray-400">Progress</span>
                                                    <span class="font-medium">25%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                    <div class="bg-green-500 h-2 rounded-full" style="width: 25%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }
                                endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- All Courses -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold">Browse All Courses</h2>
                            <div class="relative">
                                <input type="text" placeholder="Search courses..." 
                                       class="pl-8 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 text-sm">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($courses as $course): 
        // Safe array checks for course status
        $is_completed = isset($_SESSION['user_profile']['completed_courses']) && 
                       is_array($_SESSION['user_profile']['completed_courses']) && 
                       in_array($course['id'], $_SESSION['user_profile']['completed_courses']);
        
        $is_in_progress = isset($_SESSION['user_profile']['in_progress_courses']) && 
                         is_array($_SESSION['user_profile']['in_progress_courses']) && 
                         in_array($course['id'], $_SESSION['user_profile']['in_progress_courses']);
    ?>
        <div class="course-card border border-gray-200 dark:border-gray-700 rounded-lg p-5 hover:border-primary-300 dark:hover:border-primary-500 <?= $is_completed ? 'bg-gray-50 dark:bg-gray-700' : '' ?>">
            <div class="flex justify-between items-start mb-3">
                <span class="text-xs px-2 py-1 rounded-full difficulty-<?= $course['difficulty'] ?>">
                    <?= ucfirst($course['difficulty']) ?>
                </span>
                <?php if ($is_completed): ?>
                    <span class="text-xs bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200 px-2 py-1 rounded">
                        Completed
                    </span>
                <?php elseif ($is_in_progress): ?>
                    <span class="text-xs bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-200 px-2 py-1 rounded">
                        In Progress
                    </span>
                <?php endif; ?>
            </div>
            <h3 class="font-semibold mb-2 <?= $is_completed ? 'text-gray-500 dark:text-gray-400' : '' ?>">
                <?= htmlspecialchars($course['title']) ?>
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                <?= isset($course['description']) ? htmlspecialchars($course['description']) : 'No description available' ?>
            </p>
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    <i class="far fa-clock mr-1"></i><?= $course['duration'] ?> weeks
                </span>
                <form method="POST">
                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                    <?php if ($is_completed): ?>
                        <span class="text-xs text-green-600 dark:text-green-400">
                            <i class="fas fa-check-circle mr-1"></i>Completed
                        </span>
                    <?php elseif ($is_in_progress): ?>
                        <button type="submit" name="complete_course" 
                                class="text-xs text-green-600 dark:text-green-400 hover:underline">
                            Mark Complete
                        </button>
                    <?php else: ?>
                        <button type="submit" name="start_course" 
                                class="text-xs text-primary-600 dark:text-primary-400 hover:underline">
                            Start Course
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12 py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-2 mb-4 md:mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <span class="text-lg font-bold text-primary-600">LearnPath</span>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    &copy; <?= date('Y') ?> Dynamic Learning Path Recommendation System. All rights reserved.
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Dark mode toggle
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;
        
        // Check for saved user preference or use OS preference
        if (localStorage.getItem('theme') === 'dark' || 
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
        
        themeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        });
        
        // Client-side validation for interests (max 3)
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[name="interests[]"]');
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const checked = document.querySelectorAll('input[name="interests[]"]:checked');
                    if (checked.length > 3) {
                        this.checked = false;
                        alert('You can select up to 3 interests only.');
                    }
                });
            });
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        });
    </script>
</body>
</html>