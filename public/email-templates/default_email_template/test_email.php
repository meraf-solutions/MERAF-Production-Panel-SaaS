<h2 class="heading" style="color: #495057;"><?= $emailData['subject'] ?></h2>

<p class="subheading">Dear User,</p>

<p>We're writing to inform that your test email has been successful.</p>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <tr>
        <th style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left; background-color: #f8f9fa; color: #495057; font-weight: 600; text-transform: uppercase; font-size: 0.9em;">Heading</th>
        <th style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left; background-color: #f8f9fa; color: #495057; font-weight: 600; text-transform: uppercase; font-size: 0.9em;">Value</th>
    </tr>
    <tr>
        <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;">Example 1</td>
        <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;">Value 1</td>
    </tr>
    <tr>
        <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;">Example 2</td>
        <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;">Value 2</td>
    </tr>
    <tr>
        <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;">Example 3</td>
        <td style="padding: 12px 15px; border: 1px solid #dee2e6; text-align: left;">Value 3</td>
    </tr>
</table>

<?php if($emailData['message']) : ?>
    <p>
        The following is your test email message:<br>
        "<?= $emailData['message'] ?>"
    </p>
<?php endif; ?>

<p>Below texts are placeholders only to have a better view of what your email would look like.</p>

<!-- Success Box - replaced emoji with text icon -->
<div class="success-box" style="background-color: #d4edda; border-color: #c3e6cb; color: #155724; border-radius: 4px; padding: 15px; margin: 2px !important;">
    <h4 class="box-heading" style="margin-top: 0;"><span class="icon icon-success" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #28a745;">&#9989;</span> Lorem Ipsum Dolor Sit Amet</h4>
    <p class="box-content" style="margin-bottom: 0;">Consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
</div>

<!-- Warning Box - replaced emoji with text icon -->
<div class="warning-box" style="background-color: #fff3cd; border-color: #ffeeba; color: #856404; border-radius: 4px; padding: 15px; margin: 2px !important;">
	<h4 class="box-heading" style="margin-top: 0;"><span class="icon icon-warning" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #ffc107;">&#9888;</span> Ut Enim Ad Minim Veniam</h4>
	<p class="box-content" style="margin-bottom: 0;">Quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat duis aute irure dolor.</p>
</div>

<!-- Info Box - replaced emoji with text icon -->
<div class="info-box" style="background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; border-radius: 4px; padding: 15px; margin: 2px !important;">
	<h4 class="box-heading" style="margin-top: 0;"><span class="icon icon-info" style="display: inline-block; font-size: 1.5em; vertical-align: middle; margin-right: 10px; color: #17a2b8;">&#8505;</span> Excepteur Sint Occaecat</h4>
	<ol class="box-content" style="margin-bottom: 0;">
		<li>Sunt in culpa qui officia deserunt mollit</li>
		<li>Anim id est laborum et perspiciatis unde</li>
		<li>Omnis iste natus error sit voluptatem</li>
		<li>Accusantium doloremque laudantium</li>
	</ol>
</div>

<!-- Danger Box - replaced emoji with text icon -->
<div class="danger-box" style="background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; border-radius: 4px; padding: 15px; margin: 2px !important;">
	<h4 class="box-heading" style="margin-top: 0;"><span class="icon icon-danger" style="display: inline-flex; justify-content: center; align-items: center; width: 1.1em; height: 1.1em; font-size: 1.1rem; color: #dc3545;">&#10071;</span> Nemo Enim Ipsam</h4>
	<p class="box-content" style="margin-bottom: 0;">Neque porro quisquam est qui dolorem ipsum quia dolor sit amet.</p>
</div>

<!-- Notes Section -->
<div class="notes" style="font-size: 15px; color: #666; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
	<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis.</p>
</div>
