<div>
  <?php foreach ($services as $name => $service): ?>
    <div class="auth-service <?php echo $service->id; ?>">
        <?php echo CHtml::link('<i class="fa fa-'.$name.'"></i>', array($action, 'service' => $name)); ?>
    </div>
  <?php endforeach; ?>
</div>
