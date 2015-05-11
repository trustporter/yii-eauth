<div>
  <?php foreach ($services as $name => $service): ?>
    <div class="auth-service <?php echo $service->id; ?>">
        <?php $parts = explode("_", $name); ?>
        <?php echo CHtml::link('<i class="fa fa-'.$parts[0].'"></i>', array($action, 'service' => $name)); ?>
    </div>
  <?php endforeach; ?>
</div>
