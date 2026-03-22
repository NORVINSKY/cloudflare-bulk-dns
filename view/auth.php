<main class="container">
<hgroup style="text-align: center;">
    <h2><?=APP_NAME?></h2>
    <h3>Cloudflare API authenticate</h3>
</hgroup>

<?php foreach ($_LOG as $l): ?>
   <p><mark><?=$l?></mark></p>
<?php endforeach; ?>

<form method="post" action="/index.php">
    <input type="hidden" name="auth" value="true">
    <!-- Grid -->
    <div class="grid">

        <!-- Markup example 1: input is inside label -->
        <label for="email">
            <input type="email" id="email" name="email" placeholder="Email address" required>
        </label>

        <label for="apikey">
            <input type="text" id="apikey" name="apikey" placeholder="API key" required>
        </label>

    </div>
    <!-- Button -->
    <button type="submit">Submit</button>

</form>