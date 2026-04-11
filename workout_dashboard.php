<?php
declare(strict_types=1);

require __DIR__ . '/workout_common.php';

$userId = workout_user_id();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'log_day') {
        $plan = current_workout_plan($userId);
        $weekNumber = (int) ($_POST['week_number'] ?? 1);
        $dayIndex = (int) ($_POST['day_index'] ?? 0);
        if ($plan && isset($plan['weeks'][$weekNumber]['schedule'][$dayIndex])) {
            $day = $plan['weeks'][$weekNumber]['schedule'][$dayIndex];
            if ($day['type'] === 'workout') {
                $date = (string) ($_POST['workout_date'] ?? week_date_for_plan($plan, $weekNumber, $dayIndex));
                save_workout_day_log($userId, $date, $day['exercises']);
                $message = 'Workout saved successfully.';
            }
        }
    } elseif ($action === 'reset_plan') {
        reset_workout_plan($userId);
        $message = 'Your workout plan was reset. Existing logs were kept for history.';
    }
}

$plan = current_workout_plan($userId);
if (!$plan) {
    header('Location: workout_form.php');
    exit;
}

$selectedWeek = isset($_GET['week']) ? max(1, min(12, (int) $_GET['week'])) : 1;
$week = $plan['weeks'][$selectedWeek];
$weeklyProgress = weekly_progress_summary($userId, $plan, $selectedWeek);
$completedWorkouts = completed_workout_count($userId);
$exerciseLogs = exercise_log_count($userId);
$recentLogs = array_slice(workout_logs_for_user($userId), 0, 10);
if (isset($_GET['created'])) {
    $message = 'Your 12-week workout plan is ready.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Workout Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root { --sage:#5a7a5a; --sage-light:#8aad8a; --sage-pale:#e8f0e8; --sage-deep:#2d4a2d; --cream:#faf7f2; --ink:#2a2520; --muted:#7a6e66; --border:rgba(90,122,90,0.18); --shadow:0 12px 35px rgba(42,37,32,0.09); }
  * { box-sizing:border-box; margin:0; padding:0; }
  body { font-family:'DM Sans',sans-serif; background:var(--cream); color:var(--ink); min-height:100vh; }
  .topbar { background:var(--sage-deep); color:white; padding:28px 16px 32px; }
  .shell { max-width:1180px; margin:0 auto; }
  .top-links { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:18px; }
  .top-links a,.top-links button { border:none; text-decoration:none; background:rgba(255,255,255,0.12); color:white; padding:10px 14px; border-radius:999px; font-size:13px; font-family:'DM Sans',sans-serif; cursor:pointer; }
  h1 { font-family:'DM Serif Display',serif; font-size:38px; line-height:1.05; margin-bottom:8px; }
  .subtitle { color:rgba(255,255,255,0.78); max-width:720px; line-height:1.7; font-size:15px; }
  .content { padding:18px 16px 40px; }
  .flash { background:#edf8ed; color:#245124; border:1px solid #bfd9bf; border-radius:16px; padding:14px 16px; margin-bottom:16px; }
  .metrics { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:16px; }
  .metric,.panel { background:white; border-radius:20px; padding:18px; box-shadow:var(--shadow); }
  .metric strong { display:block; font-family:'DM Serif Display',serif; font-size:30px; color:var(--sage); margin-bottom:4px; }
  .metric span { font-size:13px; color:var(--muted); }
  .progress-track { width:100%; height:12px; border-radius:999px; background:#edf0ea; overflow:hidden; margin-top:12px; }
  .progress-fill { height:100%; background:linear-gradient(90deg,var(--sage-light),var(--sage)); }
  .week-nav { display:flex; gap:8px; overflow-x:auto; padding-bottom:6px; margin-bottom:16px; }
  .week-nav a { flex-shrink:0; text-decoration:none; color:var(--muted); border:1px solid var(--border); background:white; border-radius:999px; padding:9px 14px; font-size:13px; font-weight:700; }
  .week-nav a.active { background:var(--sage); color:white; border-color:var(--sage); }
  .phase-summary { display:grid; grid-template-columns:1.2fr 1fr; gap:16px; margin-bottom:16px; }
  .phase-summary h2 { font-family:'DM Serif Display',serif; font-size:28px; margin-bottom:8px; }
  .meta-list { display:grid; gap:10px; }
  .meta-item { background:var(--cream); border-radius:14px; padding:12px 14px; font-size:13px; color:var(--muted); }
  .meta-item strong { color:var(--ink); display:block; margin-bottom:4px; }
  .schedule { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; margin-bottom:16px; }
  .day-card { background:white; border-radius:20px; padding:18px; box-shadow:var(--shadow); }
  .day-card.rest { background:#f7f4ee; }
  .day-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:14px; }
  .day-name { font-family:'DM Serif Display',serif; font-size:24px; margin-bottom:4px; }
  .day-label { font-size:13px; color:var(--muted); }
  .tag { background:var(--sage-pale); color:var(--sage-deep); border-radius:999px; padding:7px 10px; font-size:12px; font-weight:700; }
  table { width:100%; border-collapse:collapse; margin-bottom:16px; }
  th,td { text-align:left; padding:10px 8px; border-bottom:1px solid #eee; font-size:13px; vertical-align:top; }
  th { color:var(--muted); font-weight:700; font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
  .day-form { display:grid; gap:10px; }
  .day-form label { font-size:12px; color:var(--muted); font-weight:700; }
  .day-form input { width:100%; border:1.5px solid var(--border); border-radius:12px; padding:12px 13px; font-family:'DM Sans',sans-serif; font-size:14px; }
  .btn-primary { border:none; border-radius:14px; padding:13px 16px; font-family:'DM Sans',sans-serif; font-size:14px; font-weight:700; cursor:pointer; background:var(--sage); color:white; }
  .logs ul { list-style:none; display:grid; gap:10px; }
  .logs li { background:var(--cream); border-radius:14px; padding:12px 14px; font-size:13px; color:var(--muted); line-height:1.6; }
  .logs strong { color:var(--ink); }
  .empty { font-size:13px; color:var(--muted); line-height:1.6; }
  @media (max-width:980px) { .metrics,.phase-summary,.schedule { grid-template-columns:1fr; } }
  @media print { .top-links,.week-nav,.day-form,.flash { display:none !important; } body { background:white; } .metric,.panel,.day-card { box-shadow:none; border:1px solid #ddd; } }
</style>
</head>
<body>
  <header class="topbar">
    <div class="shell">
      <div class="top-links">
        <a href="index.html">Home</a>
        <a href="workout_form.php">Create new plan</a>
        <button type="button" onclick="window.print()">Download plan as PDF</button>
        <form method="post" action="workout_dashboard.php" style="display:inline">
          <input type="hidden" name="csrf_token" value="<?php echo html(csrf_token()); ?>">
          <input type="hidden" name="action" value="reset_plan">
          <button type="submit" onclick="return confirm('Reset the current workout plan?')">Reset plan</button>
        </form>
      </div>
      <h1>Workout Dashboard</h1>
      <p class="subtitle">Track your generated 3-month program, log completed workouts, monitor weekly adherence, and keep your plan progression in one place.</p>
    </div>
  </header>

  <main class="content">
    <div class="shell">
      <?php if ($message !== ''): ?><div class="flash"><?php echo html($message); ?></div><?php endif; ?>

      <section class="metrics">
        <div class="metric"><strong><?php echo html((string) $completedWorkouts); ?></strong><span>Completed workouts logged</span></div>
        <div class="metric"><strong><?php echo html((string) $exerciseLogs); ?></strong><span>Total exercises logged</span></div>
        <div class="metric"><strong><?php echo html((string) $weeklyProgress['completed']); ?>/<?php echo html((string) $weeklyProgress['planned']); ?></strong><span>Workouts completed this week</span></div>
        <div class="metric"><strong><?php echo html((string) $weeklyProgress['percent']); ?>%</strong><span>Weekly progress</span><div class="progress-track"><div class="progress-fill" style="width: <?php echo html((string) $weeklyProgress['percent']); ?>%"></div></div></div>
      </section>

      <nav class="week-nav">
        <?php for ($i = 1; $i <= 12; $i++): ?>
          <a href="workout_dashboard.php?week=<?php echo $i; ?>" class="<?php echo $selectedWeek === $i ? 'active' : ''; ?>">Week <?php echo $i; ?></a>
        <?php endfor; ?>
      </nav>

      <section class="phase-summary">
        <div class="panel">
          <h2><?php echo html($week['phase_title']); ?></h2>
          <p style="line-height:1.7; color: var(--muted); margin-bottom: 14px;"><?php echo html($plan['phases'][$week['phase']]['focus']); ?></p>
          <div class="meta-list">
            <div class="meta-item"><strong>Split</strong><?php echo html($week['split']); ?></div>
            <div class="meta-item"><strong>Training profile</strong>Goal: <?php echo html($plan['meta']['goal']); ?>, Experience: <?php echo html($plan['meta']['experience']); ?>, Location: <?php echo html($plan['meta']['location']); ?>.</div>
            <div class="meta-item"><strong>Availability</strong><?php echo html((string) $plan['meta']['days_per_week']); ?> days per week, <?php echo html((string) $plan['meta']['time_per_session']); ?> minutes per session.</div>
          </div>
        </div>
        <div class="panel logs">
          <h2 style="font-family:'DM Serif Display',serif; font-size:24px; margin-bottom: 14px;">Recent activity</h2>
          <?php if (!$recentLogs): ?>
            <div class="empty">No workouts logged yet. Use the day cards below and tap “Mark as done” after finishing a session.</div>
          <?php else: ?>
            <ul>
              <?php foreach ($recentLogs as $log): ?>
                <li><strong><?php echo html($log['exercise_name']); ?></strong><br><?php echo html($log['date']); ?>, <?php echo html((string) $log['sets_completed']); ?> sets, <?php echo html((string) $log['reps_completed']); ?> reps</li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </section>

      <section class="schedule">
        <?php foreach ($week['schedule'] as $dayIndex => $day): ?>
          <article class="day-card <?php echo $day['type'] === 'rest' ? 'rest' : ''; ?>">
            <div class="day-head">
              <div><div class="day-name"><?php echo html($day['day']); ?></div><div class="day-label"><?php echo html($day['label']); ?></div></div>
              <div class="tag"><?php echo html($day['type'] === 'workout' ? $day['intensity'] : 'Recovery'); ?></div>
            </div>
            <?php if ($day['type'] === 'workout'): ?>
              <table>
                <thead><tr><th>Exercise</th><th>Sets</th><th>Reps</th><th>Rest</th><th>Level</th><th>Category</th></tr></thead>
                <tbody>
                  <?php foreach ($day['exercises'] as $exercise): ?>
                    <tr>
                      <td><?php echo html($exercise['name']); ?></td>
                      <td><?php echo html((string) $exercise['sets']); ?></td>
                      <td><?php echo html((string) $exercise['reps']); ?></td>
                      <td><?php echo html($exercise['rest_time']); ?></td>
                      <td><?php echo html($exercise['difficulty']); ?></td>
                      <td><?php echo html($exercise['category']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <form class="day-form" method="post" action="workout_dashboard.php?week=<?php echo $selectedWeek; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo html(csrf_token()); ?>">
                <input type="hidden" name="action" value="log_day">
                <input type="hidden" name="week_number" value="<?php echo $selectedWeek; ?>">
                <input type="hidden" name="day_index" value="<?php echo $dayIndex; ?>">
                <label for="workout-date-<?php echo $dayIndex; ?>">Workout date</label>
                <input id="workout-date-<?php echo $dayIndex; ?>" type="date" name="workout_date" value="<?php echo html(week_date_for_plan($plan, $selectedWeek, $dayIndex)); ?>">
                <button class="btn-primary" type="submit">Mark as done</button>
              </form>
            <?php else: ?>
              <div class="empty"><?php echo html($day['recovery_note']); ?></div>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </section>
    </div>
  </main>
</body>
</html>
