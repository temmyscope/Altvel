
<?php $__env->startSection('title', 'Login'); ?>
<?php $__env->startSection('content'); ?>

	<?php use App\Helpers\HTML; ?>

	<?= HTML::Card('Login'); ?>

	<?= HTML::generateForm('login', [
			'email' => [ 'rule' => 'required', 'type'=>'email', 'placeholder' => 'user@example.com'],
			'password' => ['rule' => 'required', 'type' => 'password', 'placeholder' =>'*********'],
			'rememberMe' => ['type' => 'rememberMe'],
			'login' => [ 'type' => 'submit']
		]);
	?>
	<div class='col-md-4 col-form-label text-md-left'>
		<span> <a href="<?php echo e(route('login')); ?>">Login</a> | <a href="<?php echo e(route('forgot_password')); ?>">Forgot password </a> </span>
	</div>
	
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\altvel\public\view/auth/login.blade.php ENDPATH**/ ?>