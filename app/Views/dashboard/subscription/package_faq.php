
<div class="card mb-3">
	<div class="card-header">
		<h3 class="card-title"><?= lang('Pages.Frequently_Asked_Questions') ?></h3>
	</div>
	<div class="card-body">
		<div class="accordion mt-4" id="buyingquestion">
			<div class="accordion-item rounded">
				<h2 class="accordion-header" id="headingOne">
					<button class="accordion-button border-0 bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne"
						aria-expanded="true" aria-controls="collapseOne">
						<?= lang('Pages.How_does_billing_work') ?>
					</button>
				</h2>
				<div id="collapseOne" class="accordion-collapse border-0 collapse show" aria-labelledby="headingOne"
					data-bs-parent="#buyingquestion">
					<div class="accordion-body text-muted">
						<?= lang('Pages.How_does_billing_work_answer') ?>
					</div>
				</div>
			</div>
			
			<div class="accordion-item rounded mt-2">
				<h2 class="accordion-header" id="headingTwo">
					<button class="accordion-button border-0 bg-light collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo"
						aria-expanded="false" aria-controls="collapseTwo">
						<?= lang('Pages.Can_I_upgrade_or_downgrade_my_plan') ?>
					</button>
				</h2>
				<div id="collapseTwo" class="accordion-collapse border-0 collapse" aria-labelledby="headingTwo"
					data-bs-parent="#buyingquestion">
					<div class="accordion-body text-muted">
						<?= lang('Pages.Can_I_upgrade_or_downgrade_my_plan_answer') ?>
					</div>
				</div>
			</div>

			<div class="accordion-item rounded mt-2">
				<h2 class="accordion-header" id="headingThree">
					<button class="accordion-button border-0 bg-light collapsed" type="button" data-bs-toggle="collapse"
						data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
						<?= lang('Pages.What_payment_methods_do_you_accept') ?>
					</button>
				</h2>
				<div id="collapseThree" class="accordion-collapse border-0 collapse" aria-labelledby="headingThree"
					data-bs-parent="#buyingquestion">
					<div class="accordion-body text-muted">
						<?= lang('Pages.What_payment_methods_do_you_accept_asnwer') ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>