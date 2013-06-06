<div>
    <?= $test; ?>
    <?php if($this->startCache("myid")): ?>
        <div><?= time(); ?></div>
        <?php $this->alwaysEvaluate("\Application\Controllers\Home::fetchUser"); ?>
    <?php $this->endCache(); ?>
    <?php endif; ?>

</div>