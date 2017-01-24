# php-form
the amazing HTML form building class

```
// define dropdown/radio/checkbox options
$dropdown_salutation = array(''=>'[Please select]','Mr'=>'Mr','Mrs'=>'Mrs','Ms'=>'Ms','Dr'=>'Dr','Prof.'=>'Prof.');
$dropdown_confirm = array('Accepted'=>'I have read and accept the terms');
$dropdown_opt = array('A'=>'Alpha','B'=>'Beta','G'=>'Gamma','D'=>'Delta');

// create delegate form
$f = new form('delegate');

// give the form a human name
$f->set_attr('caption','Delegate');

// use font-awesome icon
$ico = "<i class='fa fa-user'></i>";

// define the form elements
$f->message('header',"$ico Delegate information",'span');

$f->field('salutation',"Salutation",'dropdown',$dropdown_salutation);
$f->field('first_name',"First Name",'text');
$f->field('last_name',"Last Name",'text');
$f->field('address',"Address",'textarea');
$f->field('email',"Email Address",'email')->dataFormat(REGEX_EMAIL);
$f->field('telephone',"Mobile / Telephone",'text')->dataFormat(REGEX_PHONE);
$f->field('opt1',"Radio options",'radio',$dropdown_opt);
$f->field('opt2',"Terms acceptance",'checkbox',$dropdown_confirm);

// set which fields should be mandatory by default
$f->set_mandatory('all',true);
$f->set_mandatory('telephone',false);

// render the form in HTML
echo "<form action='app.php' method='post'>{$f}</form>";
```

```
<form action='app.php' method='post'>
<div class='form delegate'>
	<div class='form_row header'>
		<div class='form_message header'><span><i class='fa fa-user'></i> Delegate information</span></div>
	</div>
	<div class='form_row salutation'>
		<div class='form_caption salutation'><span class="mandatory">Salutation</span></div>
		<div class='form_field salutation'><select id="field_salutation" name="salutation" title="Salutation" class="mandatory input"><option selected="selected" value="">[Please select]</option><option value="Mr">Mr</option><option value="Mrs">Mrs</option><option value="Ms">Ms</option><option value="Dr">Dr</option><option value="Prof.">Prof.</option></select></div>
	</div>
	<div class='form_row first_name'>
		<div class='form_caption first_name'><span class="mandatory">First Name</span></div>
		<div class='form_field first_name'><input id="field_first_name" name="first_name" title="First Name" type="text" class="mandatory input" /></div>
	</div>
	<div class='form_row last_name'>
		<div class='form_caption last_name'><span class="mandatory">Last Name</span></div>
		<div class='form_field last_name'><input id="field_last_name" name="last_name" title="Last Name" type="text" class="mandatory input" /></div>
	</div>
	<div class='form_row address'>
		<div class='form_caption address'><span class="mandatory">Address</span></div>
		<div class='form_field address'><textarea id="field_address" name="address" title="Address" type="textarea" class="mandatory input" cols="24" rows="5"></textarea></div>
	</div>
	<div class='form_row email'>
		<div class='form_caption email'><span class="mandatory">Email Address</span></div>
		<div class='form_field email'><input id="field_email" name="email" title="Email Address" type="text" class="mandatory input" placeholder="user@company.com" /></div>
	</div>
	<div class='form_row telephone'>
		<div class='form_caption telephone'><span class="mandatory">Mobile / Telephone</span></div>
		<div class='form_field telephone'><input id="field_telephone" name="telephone" title="Mobile / Telephone" type="text" class="mandatory input" /></div>
	</div>
	<div class='form_row opt1'>
		<div class='form_caption opt1'><span class="mandatory">Radio options</span></div>
		<div class='form_field opt1'><ul class='radio'>
			<li><input id="opt1_A" name="opt1" title="Alpha" type="radio" class="mandatory input" value="A" /> <label for="opt1_A">Alpha</label></li>
			<li><input id="opt1_B" name="opt1" title="Beta" type="radio" class="mandatory input" value="B" /> <label for="opt1_B">Beta</label></li>
			<li><input id="opt1_G" name="opt1" title="Gamma" type="radio" class="mandatory input" value="G" /> <label for="opt1_G">Gamma</label></li>
			<li><input id="opt1_D" name="opt1" title="Delta" type="radio" class="mandatory input" value="D" /> <label for="opt1_D">Delta</label></li>
		</ul></div>
	</div>
	<div class='form_row opt2'>
		<div class='form_caption opt2'><span class="mandatory">Terms acceptance</span></div>
		<div class='form_field opt2'><ul class='checkbox'>
			<li><input id="opt2_Accepted" name="opt2[]" title="I have read and accept the terms" type="checkbox" class="mandatory input" value="Accepted" /> <label for="opt2_Accepted">I have read and accept the terms</label></li>
		</ul></div>
	</div>
<div class='clear'></div>
</div>
```
