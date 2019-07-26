<?= view($config->views['header'], ['current' => 'workflows']) ?>

<style>
.remove-icon {
	cursor: pointer;
}
.sort-handle {
	cursor: move;
	cursor: -webkit-grabbing;
	margin-right: 20px;
}
</style>

	<h2>New Workflow</h2>

<?php
if (empty($tasks)):
?>
	<p>There are no tasks defined. Please add some tasks before defining a workflow.</p>
<?php
	return;
endif;
?>

	<form name="create-workflow" action="<?= site_url('workflows') ?>" method="post" onsubmit="this.tasks.value = sortable.toArray();">
		<input class="btn btn-primary float-right" type="submit" value="Submit">
		<input name="tasks" type="hidden" value="" />
		
		<div class="row mt-4">
			<div class="col-sm-4">
				<h3>Details</h3>
				<div class="form-group">
					<label for="category">Category</label>
					<input name="category" type="text" class="form-control" id="category" aria-describedby="categoryHelp" placeholder="Workflow category" value="<?= old('category') ?>">
					<small id="categoryHelp" class="form-text text-muted">A generalized group to organize workflows.</small>
				</div>
				<div class="form-group">
					<label for="name">Name</label>
					<input name="name" type="text" class="form-control" id="name" aria-describedby="nameHelp" placeholder="Workflow name" value="<?= old('name') ?>" required>
					<small id="nameHelp" class="form-text text-muted">A short descriptive name to identify this workflow.</small>
				</div>
				<div class="form-group">
					<label for="icon">Icon</label>
					<input name="icon" type="text" class="form-control" id="icon" aria-describedby="iconHelp" placeholder="Workflow icon" value="<?= old('icon') ?>">
					<small id="iconHelp" class="form-text text-muted">An icon class for this workflow (usually: FontAwesome).</small>
				</div>
				<div class="form-group">
					<label for="summary">Summary</label>
					<input name="summary" type="text" class="form-control" id="icon" aria-describedby="summaryHelp" placeholder="Workflow summary" value="<?= old('summary') ?>" required>
					<small id="summaryHelp" class="form-text text-muted">A brief summary of this workflow's usage.</small>
				</div>
				<div class="form-group">
					<label for="description">Description</label>
					<textarea name="description" class="form-control" id="description" rows="3" aria-describedby="descriptionHelp" placeholder="Workflow description"><?= old('description') ?></textarea>
					<small id="descriptionHelp" class="form-text text-muted">A full description or instructions for using this workflow.</small>
				</div>
			</div>
		
			<div class="col-sm-8">
				<h3>Tasks</h3>
				<div id="tasksSelect" class="mb-4">
<?php
foreach ($tasks as $task):
?>
					<button type="button" class="btn btn-outline-primary" onclick="addTask(<?= $task->id ?>);">
						<i class="fas fa-plus-circle"></i>
						<?= $task->name ?>
						<small class="text-muted">(<?= $task->uid ?>)</small>
					</button>
<?php
endforeach;
?>			
				</div>
			
				<div id="tasksList" class="list-group">
				
<?php
foreach (explode(',', old('tasks')) as $taskId):
	foreach ($tasks as $task):
		if ($task->id == $taskId):
?>
					<div class="list-group-item" data-id="<?= $task->id ?>">
						<span class="remove-icon float-right" onclick="this.parentNode.remove();"><i class="fas fa-minus-circle"></i></span>
						<span class="sort-handle" aria-hidden="true"><i class="fas fa-arrows-alt-v"></i></span>
						<i class="fas <?= $task->icon ?>"></i>
						<span class="font-weight-bold mr-3"><?= $task->name ?></span>
						<small class="text-muted"><?= $task->summary ?></small>
					</div>
<?php
			break;
		endif;
	endforeach;
endforeach;
?>
				</div>
			</div>
		</form>
	</div>

<script>
var sortable;
$(document).ready(function() {
	var sortList = document.getElementById('tasksList');
	sortable = new Sortable.create(sortList, {
	  handle: '.sort-handle',
	  animation: 150
	});
});

function addTask(taskId) {
	task = tasks[taskId];
	
	html  = '<div class="list-group-item" data-id="' + task['id'] + '" onclick="this.remove();"> ';
	html += '<span class="remove-icon float-right" onclick="this.parentNode.remove();"><i class="fas fa-minus-circle"></i></span>';
	html += '<span class="sort-handle" aria-hidden="true"><i class="fas fa-arrows-alt-v"></i></span> '
	html += '<i class="fas ' + task['icon'] + '"></i> ';
	html += '<span class="font-weight-bold mr-3">' + task['name'] +'</span> ';
	html += '<small class="text-muted">' + task['summary'] + '</small> ';
	html += '</div>';
	
	$('#tasksList').append(html);
}

var tasks = <?= $json ?>;

</script>

<?= view($config->views['footer']) ?>
