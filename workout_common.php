<?php
declare(strict_types=1);

session_start();

const WORKOUT_DB_FILE = __DIR__ . '/app_data/workout_planner.sqlite';
const WORKOUT_SCHEMA_FILE = __DIR__ . '/schema.sql';

function workout_db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dir = dirname(WORKOUT_DB_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . WORKOUT_DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $schema = file_get_contents(WORKOUT_SCHEMA_FILE);
    if ($schema === false) {
        throw new RuntimeException('Unable to read schema file.');
    }
    $pdo->exec($schema);

    return $pdo;
}

function workout_user_id(): int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 1;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(422);
        exit('Invalid request token.');
    }
}

function html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function workout_exercise_database(): array
{
    return [
        ['name' => 'Squats', 'difficulty' => 'Beginner', 'category' => 'Legs', 'locations' => ['Gym', 'Home'], 'phases' => [1, 2, 3]],
        ['name' => 'Bench Press', 'difficulty' => 'Intermediate', 'category' => 'Push', 'locations' => ['Gym'], 'phases' => [1, 2, 3]],
        ['name' => 'Push-Ups', 'difficulty' => 'Beginner', 'category' => 'Push', 'locations' => ['Home', 'Gym'], 'phases' => [1, 2, 3]],
        ['name' => 'Deadlift', 'difficulty' => 'Advanced', 'category' => 'Pull', 'locations' => ['Gym'], 'phases' => [2, 3]],
        ['name' => 'Lat Pulldown', 'difficulty' => 'Beginner', 'category' => 'Pull', 'locations' => ['Gym'], 'phases' => [1, 2, 3]],
        ['name' => 'Resistance Band Row', 'difficulty' => 'Beginner', 'category' => 'Pull', 'locations' => ['Home'], 'phases' => [1, 2, 3]],
        ['name' => 'Shoulder Press', 'difficulty' => 'Intermediate', 'category' => 'Push', 'locations' => ['Gym', 'Home'], 'phases' => [1, 2, 3]],
        ['name' => 'Bicep Curl', 'difficulty' => 'Beginner', 'category' => 'Pull', 'locations' => ['Gym', 'Home'], 'phases' => [1, 2, 3]],
        ['name' => 'Tricep Pushdown', 'difficulty' => 'Beginner', 'category' => 'Push', 'locations' => ['Gym'], 'phases' => [1, 2, 3]],
        ['name' => 'Overhead Tricep Extension', 'difficulty' => 'Beginner', 'category' => 'Push', 'locations' => ['Home'], 'phases' => [1, 2, 3]],
        ['name' => 'Plank', 'difficulty' => 'Beginner', 'category' => 'Cardio', 'locations' => ['Gym', 'Home'], 'phases' => [1, 2, 3]],
        ['name' => 'Walking', 'difficulty' => 'Beginner', 'category' => 'Cardio', 'locations' => ['Gym', 'Home'], 'phases' => [1, 2, 3]],
        ['name' => 'HIIT Intervals', 'difficulty' => 'Advanced', 'category' => 'Cardio', 'locations' => ['Gym', 'Home'], 'phases' => [3]],
        ['name' => 'Walking Lunges', 'difficulty' => 'Intermediate', 'category' => 'Legs', 'locations' => ['Gym', 'Home'], 'phases' => [2, 3]],
        ['name' => 'Romanian Deadlift', 'difficulty' => 'Intermediate', 'category' => 'Legs', 'locations' => ['Gym', 'Home'], 'phases' => [2, 3]],
        ['name' => 'Glute Bridge', 'difficulty' => 'Beginner', 'category' => 'Legs', 'locations' => ['Gym', 'Home'], 'phases' => [1, 2, 3]],
        ['name' => 'Step-Ups', 'difficulty' => 'Beginner', 'category' => 'Legs', 'locations' => ['Gym', 'Home'], 'phases' => [1, 2]],
        ['name' => 'Incline Dumbbell Press', 'difficulty' => 'Intermediate', 'category' => 'Push', 'locations' => ['Gym', 'Home'], 'phases' => [2, 3]],
        ['name' => 'Cable Fly', 'difficulty' => 'Intermediate', 'category' => 'Push', 'locations' => ['Gym'], 'phases' => [3]],
        ['name' => 'Lateral Raise', 'difficulty' => 'Intermediate', 'category' => 'Push', 'locations' => ['Gym', 'Home'], 'phases' => [2, 3]],
        ['name' => 'Seated Cable Row', 'difficulty' => 'Intermediate', 'category' => 'Pull', 'locations' => ['Gym'], 'phases' => [2, 3]],
        ['name' => 'Single-Arm Dumbbell Row', 'difficulty' => 'Intermediate', 'category' => 'Pull', 'locations' => ['Gym', 'Home'], 'phases' => [2, 3]],
        ['name' => 'Face Pull', 'difficulty' => 'Intermediate', 'category' => 'Pull', 'locations' => ['Gym', 'Home'], 'phases' => [2, 3]],
        ['name' => 'Leg Press', 'difficulty' => 'Intermediate', 'category' => 'Legs', 'locations' => ['Gym'], 'phases' => [2, 3]],
        ['name' => 'Bulgarian Split Squat', 'difficulty' => 'Advanced', 'category' => 'Legs', 'locations' => ['Gym', 'Home'], 'phases' => [3]],
        ['name' => 'Mountain Climbers', 'difficulty' => 'Intermediate', 'category' => 'Cardio', 'locations' => ['Gym', 'Home'], 'phases' => [2, 3]],
        ['name' => 'Cycling', 'difficulty' => 'Beginner', 'category' => 'Cardio', 'locations' => ['Gym', 'Home'], 'phases' => [1, 2, 3]],
    ];
}

function workout_phase_config(int $phase, array $input): array
{
    $goal = $input['goal'];
    $experience = $input['experience'];
    $minutes = max(30, (int) $input['time_per_session']);
    $configs = [
        1 => ['title' => 'Phase 1: Foundation', 'weeks' => 'Weeks 1-4', 'intensity' => 'Low intensity', 'focus' => 'Full body workouts, movement quality, and consistency.', 'sets' => 2, 'reps' => '10-12', 'rest' => 75, 'cardio_minutes' => min($minutes, 18), 'split' => 'Full Body'],
        2 => ['title' => 'Phase 2: Build', 'weeks' => 'Weeks 5-8', 'intensity' => 'Moderate intensity', 'focus' => 'Upper/lower split with progressive overload.', 'sets' => 3, 'reps' => '8-12', 'rest' => 60, 'cardio_minutes' => min($minutes, 22), 'split' => 'Upper / Lower'],
        3 => ['title' => 'Phase 3: Performance', 'weeks' => 'Weeks 9-12', 'intensity' => 'High intensity', 'focus' => 'Push/pull/legs split with advanced lifts and HIIT.', 'sets' => 4, 'reps' => '6-10', 'rest' => 45, 'cardio_minutes' => min($minutes, 28), 'split' => 'Push / Pull / Legs'],
    ];
    $config = $configs[$phase];
    if ($experience === 'Advanced' && $phase > 1) {
        $config['sets']++;
    } elseif ($experience === 'Beginner' && $phase === 1) {
        $config['rest'] = 90;
    }
    if ($goal === 'Weight Loss') {
        $config['cardio_minutes'] += ($phase === 3 ? 6 : 4);
    } elseif ($goal === 'Muscle Gain') {
        $config['reps'] = $phase === 3 ? '6-8' : '8-10';
        $config['rest'] += 15;
    } else {
        $config['cardio_minutes'] += 2;
    }
    return $config;
}

function workout_lookup(string $category, string $location, int $phase, array $used = []): ?array
{
    foreach (workout_exercise_database() as $exercise) {
        if ($exercise['category'] !== $category || !in_array($location, $exercise['locations'], true) || !in_array($phase, $exercise['phases'], true) || in_array($exercise['name'], $used, true)) {
            continue;
        }
        return $exercise;
    }
    foreach (workout_exercise_database() as $exercise) {
        if ($exercise['category'] === $category && in_array($phase, $exercise['phases'], true)) {
            return $exercise;
        }
    }
    return null;
}

function workout_cardio_name(int $phase, string $goal, string $location): string
{
    if ($phase === 3) {
        return 'HIIT Intervals';
    }
    if ($goal === 'Weight Loss') {
        return $location === 'Gym' ? 'Cycling' : 'Walking';
    }
    return 'Walking';
}

function workout_exercise_block(string $category, int $phase, array $input, array &$used, ?string $forcedName = null): array
{
    $config = workout_phase_config($phase, $input);
    $location = $input['location'];
    $minutes = max(30, (int) $input['time_per_session']);
    $exercise = null;
    if ($forcedName !== null) {
        foreach (workout_exercise_database() as $candidate) {
            if ($candidate['name'] === $forcedName) {
                $exercise = $candidate;
                break;
            }
        }
    }
    if (!$exercise) {
        $exercise = workout_lookup($category, $location, $phase, $used);
    }
    if (!$exercise) {
        throw new RuntimeException('Unable to build exercise list for ' . $category . '.');
    }
    $used[] = $exercise['name'];
    $sets = $config['sets'];
    $reps = $config['reps'];
    $rest = $config['rest'];
    if ($exercise['category'] === 'Cardio') {
        $sets = 1;
        $reps = $config['cardio_minutes'] . ' min';
        $rest = 30;
        if ($exercise['name'] === 'HIIT Intervals') {
            $reps = max(12, min($minutes, $config['cardio_minutes'])) . ' min';
            $rest = 20;
        }
    }
    return ['name' => $exercise['name'], 'sets' => $sets, 'reps' => $reps, 'rest_time' => $rest . ' sec', 'difficulty' => $exercise['difficulty'], 'category' => $exercise['category']];
}

function workout_day_template(int $phase, int $daysPerWeek): array
{
    if ($phase === 1) {
        $templates = [
            ['label' => 'Full Body A', 'type' => 'workout', 'categories' => ['Legs', 'Push', 'Pull', 'Cardio']],
            ['label' => 'Recovery', 'type' => 'rest'],
            ['label' => 'Full Body B', 'type' => 'workout', 'categories' => ['Legs', 'Push', 'Pull', 'Cardio']],
            ['label' => 'Mobility', 'type' => 'rest'],
            ['label' => 'Full Body C', 'type' => 'workout', 'categories' => ['Legs', 'Push', 'Pull', 'Cardio']],
            ['label' => 'Conditioning', 'type' => $daysPerWeek >= 4 ? 'workout' : 'rest', 'categories' => ['Legs', 'Push', 'Pull', 'Cardio']],
            ['label' => 'Rest', 'type' => 'rest'],
        ];
        if ($daysPerWeek >= 5) {
            $templates[1] = ['label' => 'Light Cardio', 'type' => 'workout', 'categories' => ['Cardio', 'Cardio']];
        }
        if ($daysPerWeek >= 6) {
            $templates[3] = ['label' => 'Core and Cardio', 'type' => 'workout', 'categories' => ['Push', 'Pull', 'Cardio']];
        }
        return $templates;
    }
    if ($phase === 2) {
        return [
            ['label' => 'Upper A', 'type' => 'workout', 'categories' => ['Push', 'Push', 'Pull', 'Pull', 'Cardio']],
            ['label' => 'Lower A', 'type' => 'workout', 'categories' => ['Legs', 'Legs', 'Legs', 'Cardio']],
            ['label' => 'Recovery', 'type' => $daysPerWeek >= 5 ? 'workout' : 'rest', 'categories' => ['Cardio', 'Push']],
            ['label' => 'Upper B', 'type' => 'workout', 'categories' => ['Push', 'Pull', 'Push', 'Pull', 'Cardio']],
            ['label' => 'Lower B', 'type' => 'workout', 'categories' => ['Legs', 'Legs', 'Legs', 'Cardio']],
            ['label' => 'Conditioning', 'type' => $daysPerWeek >= 6 ? 'workout' : 'rest', 'categories' => ['Cardio', 'Cardio', 'Push']],
            ['label' => 'Rest', 'type' => 'rest'],
        ];
    }
    return [
        ['label' => 'Push', 'type' => 'workout', 'categories' => ['Push', 'Push', 'Push', 'Cardio']],
        ['label' => 'Pull', 'type' => 'workout', 'categories' => ['Pull', 'Pull', 'Pull', 'Cardio']],
        ['label' => 'Legs', 'type' => 'workout', 'categories' => ['Legs', 'Legs', 'Legs', 'Cardio']],
        ['label' => 'HIIT', 'type' => $daysPerWeek >= 4 ? 'workout' : 'rest', 'categories' => ['Cardio', 'Cardio']],
        ['label' => 'Upper Strength', 'type' => $daysPerWeek >= 5 ? 'workout' : 'rest', 'categories' => ['Push', 'Pull', 'Push', 'Pull']],
        ['label' => 'Legs + Core', 'type' => $daysPerWeek >= 6 ? 'workout' : 'rest', 'categories' => ['Legs', 'Legs', 'Cardio']],
        ['label' => 'Rest', 'type' => 'rest'],
    ];
}

function workout_week_schedule(int $weekNumber, array $input): array
{
    $phase = $weekNumber <= 4 ? 1 : ($weekNumber <= 8 ? 2 : 3);
    $config = workout_phase_config($phase, $input);
    $templates = workout_day_template($phase, (int) $input['days_per_week']);
    $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $schedule = [];
    foreach ($templates as $index => $template) {
        $schedule[] = ['day' => $dayNames[$index], 'label' => $template['label'], 'type' => $template['type'], 'intensity' => $config['intensity'], 'focus' => $config['focus'], 'exercises' => []];
    }
    foreach ($schedule as $index => $day) {
        if ($day['type'] !== 'workout') {
            $schedule[$index]['recovery_note'] = 'Recovery, mobility, and light walking.';
            continue;
        }
        $used = [];
        foreach ($templates[$index]['categories'] as $category) {
            $forced = $category === 'Cardio' ? workout_cardio_name($phase, $input['goal'], $input['location']) : null;
            $schedule[$index]['exercises'][] = workout_exercise_block($category, $phase, $input, $used, $forced);
        }
    }
    return $schedule;
}

function generate_workout_plan(array $input): array
{
    $plan = ['meta' => ['goal' => $input['goal'], 'experience' => $input['experience'], 'location' => $input['location'], 'days_per_week' => (int) $input['days_per_week'], 'time_per_session' => (int) $input['time_per_session'], 'generated_at' => date('c'), 'start_date' => date('Y-m-d')], 'phases' => [], 'weeks' => []];
    for ($phase = 1; $phase <= 3; $phase++) {
        $plan['phases'][$phase] = workout_phase_config($phase, $input);
    }
    for ($week = 1; $week <= 12; $week++) {
        $phase = $week <= 4 ? 1 : ($week <= 8 ? 2 : 3);
        $plan['weeks'][$week] = ['week_number' => $week, 'phase' => $phase, 'phase_title' => $plan['phases'][$phase]['title'], 'split' => $plan['phases'][$phase]['split'], 'schedule' => workout_week_schedule($week, $input)];
    }
    return $plan;
}

function validate_plan_input(array $source): array
{
    $goalOptions = ['Weight Loss', 'Muscle Gain', 'General Fitness'];
    $experienceOptions = ['Beginner', 'Intermediate', 'Advanced'];
    $locationOptions = ['Gym', 'Home'];
    $dayOptions = [3, 4, 5, 6];
    $goal = trim((string) ($source['goal'] ?? ''));
    $experience = trim((string) ($source['experience'] ?? ''));
    $location = trim((string) ($source['location'] ?? ''));
    $daysPerWeek = (int) ($source['days_per_week'] ?? 0);
    $timePerSession = (int) ($source['time_per_session'] ?? 0);
    $errors = [];
    if (!in_array($goal, $goalOptions, true)) { $errors[] = 'Please choose a valid fitness goal.'; }
    if (!in_array($experience, $experienceOptions, true)) { $errors[] = 'Please choose a valid experience level.'; }
    if (!in_array($location, $locationOptions, true)) { $errors[] = 'Please choose a valid workout location.'; }
    if (!in_array($daysPerWeek, $dayOptions, true)) { $errors[] = 'Please choose a valid number of training days.'; }
    if ($timePerSession < 20 || $timePerSession > 180) { $errors[] = 'Time per session must be between 20 and 180 minutes.'; }
    return ['errors' => $errors, 'data' => ['goal' => $goal, 'experience' => $experience, 'location' => $location, 'days_per_week' => $daysPerWeek, 'time_per_session' => $timePerSession]];
}

function save_workout_plan(int $userId, array $plan): void
{
    $json = json_encode($plan, JSON_PRETTY_PRINT);
    if ($json === false) {
        throw new RuntimeException('Unable to encode workout plan.');
    }
    $now = date('c');
    $sql = 'INSERT INTO user_workout_plans (user_id, plan_json, created_at, updated_at) VALUES (:user_id, :plan_json, :created_at, :updated_at)
            ON CONFLICT(user_id) DO UPDATE SET plan_json = excluded.plan_json, updated_at = excluded.updated_at';
    $stmt = workout_db()->prepare($sql);
    $stmt->execute([':user_id' => $userId, ':plan_json' => $json, ':created_at' => $now, ':updated_at' => $now]);
    $_SESSION['workout_plan'] = $plan;
}

function current_workout_plan(int $userId): ?array
{
    if (!empty($_SESSION['workout_plan']) && is_array($_SESSION['workout_plan'])) {
        return $_SESSION['workout_plan'];
    }
    $stmt = workout_db()->prepare('SELECT plan_json FROM user_workout_plans WHERE user_id = :user_id LIMIT 1');
    $stmt->execute([':user_id' => $userId]);
    $row = $stmt->fetch();
    if (!$row) { return null; }
    $plan = json_decode((string) $row['plan_json'], true);
    if (!is_array($plan)) { return null; }
    $_SESSION['workout_plan'] = $plan;
    return $plan;
}

function reset_workout_plan(int $userId): void
{
    $stmt = workout_db()->prepare('DELETE FROM user_workout_plans WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $userId]);
    unset($_SESSION['workout_plan']);
}

function week_date_for_plan(array $plan, int $weekNumber, int $dayIndex): string
{
    $startDate = new DateTimeImmutable($plan['meta']['start_date'] ?? date('Y-m-d'));
    $monday = $startDate->modify('monday this week');
    $offsetDays = (($weekNumber - 1) * 7) + $dayIndex;
    return $monday->modify('+' . $offsetDays . ' days')->format('Y-m-d');
}

function save_workout_day_log(int $userId, string $date, array $exercises): void
{
    $pdo = workout_db();
    $pdo->beginTransaction();
    try {
        $delete = $pdo->prepare('DELETE FROM workout_logs WHERE user_id = :user_id AND date = :date AND exercise_name = :exercise_name');
        $insert = $pdo->prepare('INSERT INTO workout_logs (user_id, date, exercise_name, sets_completed, reps_completed) VALUES (:user_id, :date, :exercise_name, :sets_completed, :reps_completed)');
        foreach ($exercises as $exercise) {
            $delete->execute([':user_id' => $userId, ':date' => $date, ':exercise_name' => $exercise['name']]);
            $repsCompleted = 0;
            if (preg_match('/(\d+)/', (string) $exercise['reps'], $matches)) {
                $repsCompleted = (int) $matches[1];
            }
            $insert->execute([':user_id' => $userId, ':date' => $date, ':exercise_name' => $exercise['name'], ':sets_completed' => (int) $exercise['sets'], ':reps_completed' => $repsCompleted]);
        }
        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function workout_logs_for_user(int $userId): array
{
    $stmt = workout_db()->prepare('SELECT * FROM workout_logs WHERE user_id = :user_id ORDER BY date DESC, id DESC');
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}

function completed_workout_count(int $userId): int
{
    $stmt = workout_db()->prepare('SELECT COUNT(DISTINCT date) AS total FROM workout_logs WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $userId]);
    $row = $stmt->fetch();
    return (int) ($row['total'] ?? 0);
}

function exercise_log_count(int $userId): int
{
    $stmt = workout_db()->prepare('SELECT COUNT(*) AS total FROM workout_logs WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $userId]);
    $row = $stmt->fetch();
    return (int) ($row['total'] ?? 0);
}

function weekly_progress_summary(int $userId, array $plan, int $weekNumber): array
{
    $week = $plan['weeks'][$weekNumber] ?? null;
    if (!$week) {
        return ['planned' => 0, 'completed' => 0, 'percent' => 0];
    }
    $planned = 0;
    $completed = 0;
    $stmt = workout_db()->prepare('SELECT COUNT(*) AS total FROM workout_logs WHERE user_id = :user_id AND date = :date');
    foreach ($week['schedule'] as $index => $day) {
        if ($day['type'] !== 'workout') { continue; }
        $planned++;
        $date = week_date_for_plan($plan, $weekNumber, $index);
        $stmt->execute([':user_id' => $userId, ':date' => $date]);
        $row = $stmt->fetch();
        if ((int) ($row['total'] ?? 0) > 0) {
            $completed++;
        }
    }
    return ['planned' => $planned, 'completed' => $completed, 'percent' => $planned > 0 ? (int) round(($completed / $planned) * 100) : 0];
}
