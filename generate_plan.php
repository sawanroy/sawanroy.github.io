<?php
declare(strict_types=1);

require __DIR__ . '/workout_common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: workout_form.php');
    exit;
}

verify_csrf();
$validated = validate_plan_input($_POST);

if ($validated['errors']) {
    $_SESSION['workout_form_errors'] = $validated['errors'];
    $_SESSION['workout_form_old'] = $validated['data'];
    header('Location: workout_form.php');
    exit;
}

$plan = generate_workout_plan($validated['data']);
save_workout_plan(workout_user_id(), $plan);

header('Location: workout_dashboard.php?week=1&created=1');
exit;
