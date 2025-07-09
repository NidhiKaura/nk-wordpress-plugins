<?php if (!defined('ABSPATH')) exit; ?>
<div id="popup-form" style="padding:30px 15px;">
    <h1>Write name of the Pages, You like to add in Site Menu (Other Than Home)</h1>
    <h2>Enter one page name per line</h2>
    <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="nk_is_submit">
        <?php wp_nonce_field('nk_is_nonce_action', 'nk_is_nonce'); ?>
        <textarea name="names" id="names" rows="5" cols="50" placeholder="Enter one page name per line" required></textarea><br />
        <br />
        <button class="button button-primary" type="submit">Submit</button>
    </form>
</div>