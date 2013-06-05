<div>
    <?= $test; ?>
    <?php if($this->startCache(array("key"=>"myid"))): ?>
        <div><?= time(); ?></div>
    <?php $this->endCache(); ?>
    <?php endif; ?>


    <?php if($this->startCache(array("key"=>"mytest"))): ?>
    <div><?= time(); ?></div>
    <?php $this->endCache(); ?>
    <?php endif; ?>
</div>