<?php include 'user_head.php' ?>
<?php if ($this->session->userdata('user_role') == 1): ?>

<?=@$result_table?>

<?php endif; ?>