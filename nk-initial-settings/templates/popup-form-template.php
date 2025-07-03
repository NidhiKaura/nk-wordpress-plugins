<?php if (!defined('ABSPATH')) exit; ?>
<div id="popup-form" class="container">
    <h3>Enter the names of additional pages <span>(excluding Home)</span> you want to create and add to the menu:</h3>
    <p class="fs-4">Enter one page title per line</p>
    <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="save_popup_form">
            <?php wp_nonce_field('save_popup_form_nonce', 'popup_form_nonce'); ?>
        <textarea name="names" id="names" rows="5" cols="50" placeholder="Enter one page name per line" required></textarea><br />
        <br />
        <button class="btn page-title-action" type="submit">Submit</button>
    </form>
</div>