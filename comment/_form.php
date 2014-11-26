<?php
/* @var $this CommentController */
/* @var $model Comment */
/* @var $form CActiveForm */
?>
<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
'id'=>'comment-form',
'enableAjaxValidation'=>false,
)); ?>
<p class="note">Fields with <span class="required">*</span> are required.</p>
<?php echo $form->errorSummary($model); ?>
<div class="row">
<?php echo $form->labelEx($model,'content', array('class'=> 'col-md-2 col-xs-12')); ?>
<?php echo $form->textArea($model,'content',array('rows'=>6,'class'=> 'col-md-8 col-xs-12')); ?>
<?php echo $form->error($model,'content'); ?>
</div>
<div class="row buttons">
<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=> 'btn btn-success pull-right')); ?>
</div>
<?php $this->endWidget(); ?>
</div>