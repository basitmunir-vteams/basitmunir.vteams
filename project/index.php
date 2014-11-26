<?php
$this->breadcrumbs=array(
	'Projects',
);

$this->menu=array(
	array('label'=>'Create Project', 'url'=>array('create')),
	array('label'=>'Manage Project', 'url'=>array('admin')),
);
?>
<?php if($sysMessage !== null):?>
	<div class="sys-message alert alert-success">
		<?php echo $sysMessage; ?>
	</div>
<?php 
	Yii::app()->clientScript->registerScript(
		'fadeAndHideEffect', '$(".sys-message").animate({opacity: 1.0}, 5000).fadeOut("slow");'
		);
	endif; ?>
<div class="panel panel-default">
 	<div class="panel-heading">
    	<h3>Projects</h3>
  	</div>
 	 <div class="panel-body">


	<?php $this->widget('zii.widgets.CListView', array(
		'dataProvider'=>$dataProvider,
		'itemView'=>'_view',
	)); ?>
  	</div>
</div>
