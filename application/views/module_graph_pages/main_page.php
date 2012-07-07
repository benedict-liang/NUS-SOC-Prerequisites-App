<div class="row modules_selection_title">
	<h1>Choose your Modules from the dropdown list below</h1>
</div>

<div id="modules_selection" class="row">
	<?php echo form_open(current_url(), array('class'=>'form-horizontal')); ?>
		<div class="controls modules_selection_controls">
			<select id="select_mods" name="select_mods">
				<!-- TODO: multiple selection -->
				<?php echo $dropdown_select_content ?>	
			</select>
			
		</div>
		<div class="controls modules_selection_controls">
			<button class="btn btn-primary mod_select_submit">Submit</button>
		</div>
		
	</form>
</div>

<div id="modules_chosen">

</div>