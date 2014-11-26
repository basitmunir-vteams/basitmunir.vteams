<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'project-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'name',array('class'=> 'col-md-2 col-xs-12') ); ?>
		<?php echo $form->textField($model,'name',array('maxlength'=>128)); ?>
		<?php echo $form->error($model,'name',array('class'=> 'error') ); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'description', array('class'=> 'col-md-2 col-xs-12')); ?>
		<?php echo $form->textArea($model,'description',array('rows'=>6, 'class'=>'col-md-8 col-xs-12')); ?>
		<?php echo $form->error($model,'description',array('class'=> 'error')); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'btn btn-success pull-right')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->