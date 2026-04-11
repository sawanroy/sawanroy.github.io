<?php
declare(strict_types=1);

require __DIR__ . '/workout_common.php';

$defaults = [
    'goal' => 'General Fitness',
    'experience' => 'Beginner',
    'location' => 'Home',
    'days_per_week' => 4,
    'time_per_session' => 45,
];

$flashErrors = $_SESSION['workout_form_errors'] ?? [];
$old = $_SESSION['workout_form_old'] ?? $defaults;
unset($_SESSION['workout_form_errors'], $_SESSION['workout_form_old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Workout Plan</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root { --sage:#5a7a5a; --sage-pale:#e8f0e8; --sage-deep:#2d4a2d; --cream:#faf7f2; --ink:#2a2520; --muted:#7a6e66; --border:rgba(90,122,90,0.18); --shadow:0 10px 35px rgba(42,37,32,0.1); }
  * { box-sizing:border-box; margin:0; padding:0; }
  body { font-family:'DM Sans',sans-serif; background:linear-gradient(180deg,#edf4ed 0%,var(--cream) 38%,#fff 100%); color:var(--ink); min-height:100vh; padding:24px 16px 40px; }
  .shell { max-width:920px; margin:0 auto; }
  .hero { background:var(--sage-deep); color:white; border-radius:26px; padding:30px 24px; margin-bottom:18px; box-shadow:var(--shadow); }
  .hero a { color:rgba(255,255,255,0.8); text-decoration:none; font-size:13px; }
  .hero h1 { font-family:'DM Serif Display',serif; font-size:38px; line-height:1.05; margin:16px 0 10px; }
  .hero p { max-width:640px; color:rgba(255,255,255,0.8); line-height:1.7; font-size:15px; }
  .card { background:white; border-radius:22px; padding:24px; box-shadow:var(--shadow); }
  .errors { background:#fff1f1; border:1px solid #f3c5c5; color:#932f2f; border-radius:16px; padding:14px 16px; margin-bottom:18px; line-height:1.6; }
  .grid { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; }
  .field { display:grid; gap:8px; }
  .field.full { grid-column:1 / -1; }
  label { font-size:13px; font-weight:700; color:var(--ink); }
  select,input { width:100%; border:1.5px solid var(--border); border-radius:14px; padding:14px 15px; font-size:15px; font-family:'DM Sans',sans-serif; background:#fff; color:var(--ink); }
  .hint { font-size:12px; color:var(--muted); line-height:1.5; }
  .actions { display:flex; gap:12px; margin-top:22px; }
  .btn { border:none; border-radius:14px; padding:14px 18px; font-size:15px; font-weight:700; font-family:'DM Sans',sans-serif; cursor:pointer; text-decoration:none; text-align:center; }
  .btn.primary { background:var(--sage); color:white; flex:1; }
  .btn.secondary { background:var(--sage-pale); color:var(--sage-deep); }
  .info { margin-top:18px; display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
  .info-card { background:var(--cream); border-radius:16px; padding:16px; }
  .info-card strong { display:block; font-size:13px; margin-bottom:6px; }
  .info-card span { font-size:12px; line-height:1.6; color:var(--muted); }
  @media (max-width:720px) { .grid,.info { grid-template-columns:1fr; } .actions { flex-direction:column; } .hero h1 { font-size:30px; } }
</style>
</head>
<body>
  <div class="shell">
    <section class="hero">
      <a href="index.html">Back to home</a>
      <h1>Workout Planner Module</h1>
      <p>Create a structured 12-week training plan that adapts to your goal, experience level, location, weekly availability, and session time.</p>
    </section>

    <section class="card">
      <?php if ($flashErrors): ?>
        <div class="errors">
          <?php foreach ($flashErrors as $error): ?>
            <div><?php echo html($error); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form action="generate_plan.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo html(csrf_token()); ?>">
        <div class="grid">
          <div class="field">
            <label for="goal">Fitness Goal</label>
            <select name="goal" id="goal" required>
              <?php foreach (['Weight Loss', 'Muscle Gain', 'General Fitness'] as $option): ?>
                <option value="<?php echo html($option); ?>" <?php echo $old['goal'] === $option ? 'selected' : ''; ?>><?php echo html($option); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="experience">Experience Level</label>
            <select name="experience" id="experience" required>
              <?php foreach (['Beginner', 'Intermediate', 'Advanced'] as $option): ?>
                <option value="<?php echo html($option); ?>" <?php echo $old['experience'] === $option ? 'selected' : ''; ?>><?php echo html($option); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="location">Workout Location</label>
            <select name="location" id="location" required>
              <?php foreach (['Gym', 'Home'] as $option): ?>
                <option value="<?php echo html($option); ?>" <?php echo $old['location'] === $option ? 'selected' : ''; ?>><?php echo html($option); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="days_per_week">Days Per Week</label>
            <select name="days_per_week" id="days_per_week" required>
              <?php foreach ([3, 4, 5, 6] as $option): ?>
                <option value="<?php echo $option; ?>" <?php echo (int) $old['days_per_week'] === $option ? 'selected' : ''; ?>><?php echo $option; ?> days</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field full">
            <label for="time_per_session">Time Per Session (minutes)</label>
            <input type="number" min="20" max="180" step="5" name="time_per_session" id="time_per_session" value="<?php echo html((string) $old['time_per_session']); ?>" required>
            <div class="hint">Choose a realistic session length. The generator uses it to balance lifting volume, cardio, and rest time.</div>
          </div>
        </div>

        <div class="actions">
          <button class="btn primary" type="submit">Generate 12-week plan</button>
          <a class="btn secondary" href="workout_dashboard.php">View current plan</a>
        </div>
      </form>

      <div class="info">
        <div class="info-card"><strong>Phase 1</strong><span>Weeks 1 to 4 focus on full body training, lower intensity, and technique-first exercise selection.</span></div>
        <div class="info-card"><strong>Phase 2</strong><span>Weeks 5 to 8 shift to upper and lower splits with more structured overload and accessory work.</span></div>
        <div class="info-card"><strong>Phase 3</strong><span>Weeks 9 to 12 introduce push, pull, and legs programming plus HIIT and higher training density.</span></div>
      </div>
    </section>
  </div>
</body>
</html>
