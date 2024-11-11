<div class="wrap">
    <h1>Color Palette Generator</h1>

    <form method="post" action="">
        <?php wp_nonce_field('generate_palette', 'palette_nonce'); ?>

        <div class="color-inputs">
            <?php for($i = 1; $i <= 4; $i++): ?>
            <div class="color-input">
                <label>Color <?php echo $i; ?>:</label>
                <input type="color" name="color[]" required>
            </div>
            <?php endfor; ?>
        </div>

        <div class="naming-preference">
            <label>Naming Preference:</label>
            <select name="naming_preference" required>
                <option value="descriptive">Descriptive Names</option>
                <option value="functional">Functional Names</option>
                <option value="both">Both</option>
            </select>
        </div>

        <button type="submit" class="button button-primary">Generate Palette</button>
    </form>
</div>
