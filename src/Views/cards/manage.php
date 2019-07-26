<?= view($config->views['header'], ['current' => 'jobs']) ?>

<?php
if (empty($job)):
	echo '<p>Unable to locate that job!</p>';
	return;
endif;
?>
	<h2>Job info</h2>
<?php
if (! $job->stage->required):
	if ($next = $job->next()):
		$route = '/' . $config->routeBase . '/' . $next->uid . '/' . $job->id;
?>
		<a class="btn btn-link float-right" href="<?= site_url($route) ?>" role="button"><i class="fas fa-arrow-circle-right"></i> Skip</a>
<?php
	endif;
endif;
?>
	<form name="update-job" action="<?= site_url("{$config->routeBase}/info/{$job->id}") ?>" method="post">
		<input class="btn btn-primary float-right" type="submit" value="Submit">
		
		<div class="row mt-4">
			<div class="col-sm-8">
				<div class="form-group">
					<label for="name">Name</label>
					<input name="name" type="text" class="form-control" id="name" aria-describedby="nameHelp" placeholder="Job name" value="<?= old('name', $job->name) ?>" required>
					<small id="nameHelp" class="form-text text-muted">A short descriptive name to identify this job.</small>
				</div>
				<div class="form-group">
					<label for="summary">Summary</label>
					<input name="summary" type="text" class="form-control" id="icon" aria-describedby="summaryHelp" placeholder="Job summary" value="<?= old('summary', $job->summary) ?>">
					<small id="summaryHelp" class="form-text text-muted">A brief summary of this job.</small>
				</div>
			</div>
		</div>
	</div>

<script>
	var baseUrl = '<?= base_url() ?>';
</script>

<?= view($config->views['footer']) ?>
