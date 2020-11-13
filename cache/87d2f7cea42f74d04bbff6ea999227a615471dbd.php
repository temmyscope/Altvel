
<?php $__env->startSection('title', 'Home'); ?>
<?php $__env->startSection('content'); ?>

	You are now logged In:

	<?php 
		dnd($home); 
	?>
	
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\altvel\public\view/home/index.blade.php ENDPATH**/ ?>