<?php

$init_session_start = true;
require("init_without_validate.php");
require_once(__DIR__.'/includes/newusercommon.php');

/**
 * TODO: handle postback:
 *  - Create user account
 *  - Enroll in teacher courses
 *  - Send email
 *  - Store upload if provided
 *  - Create request data
 *  - Adjust approvepending2
 *    - display new info
 *    - If ipeds linked to group, preselect group, or provide selector of just the linked groups
 *    - If not, establish link to selected group on approval
 *    - Create custom ipeds records for Intl requests
 */

$pagetitle = "New instructor account request";
$placeinhead = "<link rel=\"stylesheet\" href=\"$imasroot/infopages.css\" type=\"text/css\">\n";
$placeinhead .= '<script type="text/javascript" src="'.$imasroot.'/javascript/jquery.validate.min.js"></script>';
$placeinhead .= '<script type="text/javascript" src="'.$imasroot.'/javascript/ipedssearch.js"></script>';
$placeinhead .= '<style type="text/css">div { margin: 0px; padding: 0px;}</style>';
$placeinhead .= '<style>
span.form, span.formright {
    width: 100%;
    float: none;
    text-align: left;
    padding-right: 0;
    margin-bottom: 2px;
}
span.formright {
    margin-bottom: 15px;
}
br.form {
    display:none;
}
input, select {
    flex-grow: 1;
}
#newinstrform {
    width: 500px;
    margin: auto;
}

</style>';
$nologo = true;

require("header.php");
$pagetitle = "Instructor Account Request";
require("infoheader.php");

if (isset($_POST['firstname'])) {$firstname=$_POST['firstname'];} else {$firstname='';}
if (isset($_POST['lastname'])) {$lastname=$_POST['lastname'];} else {$lastname='';}
if (isset($_POST['email'])) {$email=$_POST['email'];} else {$email='';}
if (isset($_POST['phone'])) {$phone=$_POST['phone'];} else {$phone='';}
if (isset($_POST['school'])) {$school=$_POST['school'];} else {$school='';}
if (isset($_POST['verurl'])) {$verurl=$_POST['verurl'];} else {$verurl='';}
if (isset($_POST['SID'])) {$username=$_POST['SID'];} else {$username='';}

$_SESSION['challenge'] = uniqid();

?>
<form method=post id=newinstrform class=limitaftervalidate action="newinstructor.php">
<h1>New Instructor Account Request</h1>

<h2>Step 1/3: School Affiliation</h2>
<span class=form><label for="schooltype">What kind of institution do you work for?</label><br>
    <span class=small>Note: We do not provide instructor accounts to 
    parents, home-schools, or tutors</span></span>
<span class=formright><select name=schooltype id=schooltype>
    <option value="">Select...</option>
    <option value="coll">A College or University</option>
    <option value="pubk12">A Public K-12 School</option>
    <option value="privk12">A Private K-12 School</option>
    </select></span><br class=form>

<div id=locwrap style="display:none">
    <span class=form><label for=schooloc>Where is it located?</label></span>
    <span class=formright><select name=schoolloc id=schoolloc>
        <option value="">Select...</option>
        <option value="us">United States or U.S. Territories</option>
        <option value="intl">Outside the United States</option>
        </select></span><br class=form>
</div>

<div id=ussel class=selopt style="display:none">
    <span class=form>
        <span class="collsrc locdesc" style="display:none">
            Please enter the name of your institution or it's 5-digit ZIP code and click Search,
            then select your institution from the list.
        </span>
        <span class="pubk12src locdesc" style="display:none">
            Please enter the name of your school or school district and click Search,
            then select your school from the list.
        </span>
        <span class="privk12src locdesc" style="display:none">
            Please enter the name of your school and click Search,
            then select your school from the list.
        </span>
        </span>
    <span class=formright>
        <input id=searchterms aria-label="school search terms">
        <button type=button id=dosearch>Search</button>
        </span><br class=form>
    <div id=searchresultwrapper style="display:none">
        <span class=form><label for=ipeds>Select your institution:</label></span>
        <span class=formright><select name=ipeds id=ipeds></select></span><br class=form>
    </div>
</div>

<div id=intlsel class=selopt style="display:none">
    <p>MyOpenMath is based in the United States, and has policies designed to be compliant with US laws.
      If you are located outside the US, it is your responsibility to ensure that use of MyOpenMath is 
      acceptable in your jurisdication, and to gather any necessary consent from students.</p>

    <span class=form><label for=country>Select your country</label></span>
    <span class=formright><select id=country name=country>
        <option value="">Select...</option>
    <?php
    $countries = [ 'Afghanistan'=>'AF', 'Albania'=>'AL', 'Algeria'=>'DZ', 'Andorra'=>'AD', 'Angola'=>'AO', 'Anguilla'=>'AI', 'Antarctica'=>'AQ', 'Antigua and Barbuda'=>'AG', 'Argentina'=>'AR', 'Armenia'=>'AM', 'Aruba'=>'AW', 'Australia'=>'AU', 'Austria'=>'AT', 'Azerbaijan'=>'AZ', 'Bahamas (the)'=>'BS', 'Bahrain'=>'BH', 'Bangladesh'=>'BD', 'Barbados'=>'BB', 'Belarus'=>'BY', 'Belgium'=>'BE', 'Belize'=>'BZ', 'Benin'=>'BJ', 'Bermuda'=>'BM', 'Bhutan'=>'BT', 'Bolivia (Plurinational State of)'=>'BO', 'Bonaire, Sint Eustatius and Saba'=>'BQ', 'Bosnia and Herzegovina'=>'BA', 'Botswana'=>'BW', 'Bouvet Island'=>'BV', 'Brazil'=>'BR', 'British Indian Ocean Territory (the)'=>'IO', 'Brunei Darussalam'=>'BN', 'Bulgaria'=>'BG', 'Burkina Faso'=>'BF', 'Burundi'=>'BI', 'Cabo Verde'=>'CV', 'Cambodia'=>'KH', 'Cameroon'=>'CM', 'Canada'=>'CA', 'Cayman Islands (the)'=>'KY', 'Central African Republic (the)'=>'CF', 'Chad'=>'TD', 'Chile'=>'CL', 'China'=>'CN', 'Christmas Island'=>'CX', 'Cocos (Keeling) Islands (the)'=>'CC', 'Colombia'=>'CO', 'Comoros (the)'=>'KM', 'Congo (the Democratic Republic of the)'=>'CD', 'Congo (the)'=>'CG', 'Cook Islands (the)'=>'CK', 'Costa Rica'=>'CR', 'Croatia'=>'HR', 'Cuba'=>'CU', 'Curaçao'=>'CW', 'Cyprus'=>'CY', 'Czechia'=>'CZ', 'Côte d\'Ivoire'=>'CI', 'Denmark'=>'DK', 'Djibouti'=>'DJ', 'Dominica'=>'DM', 'Dominican Republic (the)'=>'DO', 'Ecuador'=>'EC', 'Egypt'=>'EG', 'El Salvador'=>'SV', 'Equatorial Guinea'=>'GQ', 'Eritrea'=>'ER', 'Estonia'=>'EE', 'Eswatini'=>'SZ', 'Ethiopia'=>'ET', 'Falkland Islands (the) [Malvinas]'=>'FK', 'Faroe Islands (the)'=>'FO', 'Fiji'=>'FJ', 'Finland'=>'FI', 'France'=>'FR', 'French Guiana'=>'GF', 'French Polynesia'=>'PF', 'French Southern Territories (the)'=>'TF', 'Gabon'=>'GA', 'Gambia (the)'=>'GM', 'Georgia'=>'GE', 'Germany'=>'DE', 'Ghana'=>'GH', 'Gibraltar'=>'GI', 'Greece'=>'GR', 'Greenland'=>'GL', 'Grenada'=>'GD', 'Guadeloupe'=>'GP', 'Guatemala'=>'GT', 'Guernsey'=>'GG', 'Guinea'=>'GN', 'Guinea-Bissau'=>'GW', 'Guyana'=>'GY', 'Haiti'=>'HT', 'Heard Island and McDonald Islands'=>'HM', 'Holy See (the)'=>'VA', 'Honduras'=>'HN', 'Hong Kong'=>'HK', 'Hungary'=>'HU', 'Iceland'=>'IS', 'India'=>'IN', 'Indonesia'=>'ID', 'Iran (Islamic Republic of)'=>'IR', 'Iraq'=>'IQ', 'Ireland'=>'IE', 'Isle of Man'=>'IM', 'Israel'=>'IL', 'Italy'=>'IT', 'Jamaica'=>'JM', 'Japan'=>'JP', 'Jersey'=>'JE', 'Jordan'=>'JO', 'Kazakhstan'=>'KZ', 'Kenya'=>'KE', 'Kiribati'=>'KI', 'Korea (the Democratic People\'s Republic of)'=>'KP', 'Korea (the Republic of)'=>'KR', 'Kuwait'=>'KW', 'Kyrgyzstan'=>'KG', 'Lao People\'s Democratic Republic (the)'=>'LA', 'Latvia'=>'LV', 'Lebanon'=>'LB', 'Lesotho'=>'LS', 'Liberia'=>'LR', 'Libya'=>'LY', 'Liechtenstein'=>'LI', 'Lithuania'=>'LT', 'Luxembourg'=>'LU', 'Macao'=>'MO', 'Madagascar'=>'MG', 'Malawi'=>'MW', 'Malaysia'=>'MY', 'Maldives'=>'MV', 'Mali'=>'ML', 'Malta'=>'MT', 'Martinique'=>'MQ', 'Mauritania'=>'MR', 'Mauritius'=>'MU', 'Mayotte'=>'YT', 'Mexico'=>'MX', 'Moldova (the Republic of)'=>'MD', 'Monaco'=>'MC', 'Mongolia'=>'MN', 'Montenegro'=>'ME', 'Montserrat'=>'MS', 'Morocco'=>'MA', 'Mozambique'=>'MZ', 'Myanmar'=>'MM', 'Namibia'=>'NA', 'Nauru'=>'NR', 'Nepal'=>'NP', 'Netherlands (the)'=>'NL', 'New Caledonia'=>'NC', 'New Zealand'=>'NZ', 'Nicaragua'=>'NI', 'Niger (the)'=>'NE', 'Nigeria'=>'NG', 'Niue'=>'NU', 'Norfolk Island'=>'NF', 'Norway'=>'NO', 'Oman'=>'OM', 'Pakistan'=>'PK', 'Palestine, State of'=>'PS', 'Panama'=>'PA', 'Papua New Guinea'=>'PG', 'Paraguay'=>'PY', 'Peru'=>'PE', 'Philippines (the)'=>'PH', 'Pitcairn'=>'PN', 'Poland'=>'PL', 'Portugal'=>'PT', 'Qatar'=>'QA', 'Republic of North Macedonia'=>'MK', 'Romania'=>'RO', 'Russian Federation (the)'=>'RU', 'Rwanda'=>'RW', 'Réunion'=>'RE', 'Saint Barthélemy'=>'BL', 'Saint Helena, Ascension and Tristan da Cunha'=>'SH', 'Saint Kitts and Nevis'=>'KN', 'Saint Lucia'=>'LC', 'Saint Martin (French part)'=>'MF', 'Saint Pierre and Miquelon'=>'PM', 'Saint Vincent and the Grenadines'=>'VC', 'Samoa'=>'WS', 'San Marino'=>'SM', 'Sao Tome and Principe'=>'ST', 'Saudi Arabia'=>'SA', 'Senegal'=>'SN', 'Serbia'=>'RS', 'Seychelles'=>'SC', 'Sierra Leone'=>'SL', 'Singapore'=>'SG', 'Sint Maarten (Dutch part)'=>'SX', 'Slovakia'=>'SK', 'Slovenia'=>'SI', 'Solomon Islands'=>'SB', 'Somalia'=>'SO', 'South Africa'=>'ZA', 'South Georgia and the South Sandwich Islands'=>'GS', 'South Sudan'=>'SS', 'Spain'=>'ES', 'Sri Lanka'=>'LK', 'Sudan (the)'=>'SD', 'Suriname'=>'SR', 'Svalbard and Jan Mayen'=>'SJ', 'Sweden'=>'SE', 'Switzerland'=>'CH', 'Syrian Arab Republic'=>'SY', 'Taiwan'=>'TW', 'Tajikistan'=>'TJ', 'Tanzania, United Republic of'=>'TZ', 'Thailand'=>'TH', 'Timor-Leste'=>'TL', 'Togo'=>'TG', 'Tokelau'=>'TK', 'Tonga'=>'TO', 'Trinidad and Tobago'=>'TT', 'Tunisia'=>'TN', 'Turkey'=>'TR', 'Turkmenistan'=>'TM', 'Turks and Caicos Islands (the)'=>'TC', 'Tuvalu'=>'TV', 'Uganda'=>'UG', 'Ukraine'=>'UA', 'United Arab Emirates (the)'=>'AE', 'United Kingdom of Great Britain and Northern Ireland (the)'=>'GB', 'United States Minor Outlying Islands (the)'=>'UM', 'Uruguay'=>'UY', 'Uzbekistan'=>'UZ', 'Vanuatu'=>'VU', 'Venezuela (Bolivarian Republic of)'=>'VE', 'Viet Nam'=>'VN', 'Virgin Islands (British)'=>'VG', 'Wallis and Futuna'=>'WF', 'Western Sahara'=>'EH', 'Yemen'=>'YE', 'Zambia'=>'ZM', 'Zimbabwe'=>'ZW', 'Åland Islands'=>'AX'];
    foreach ($countries as $name=>$code) {
        echo '<option value='.$code.'>'.$name.'</option>';
    }
    ?>
    </select></span><br class=form>
    <div id=intlwrap style="display:none">
        <span class=form><label for=intlipeds>Select your institution or affiliation:</label></span>
        <span class=formright><select name=intlipeds id=intlipeds></select></span><br class=form>
    </div>
</div>

<div id=otherschool style="display:none">
    <span class=form><label for=otherschool>Please give the name of your school:</label></span>
    <span class=formright><input name=otherschool size=40 /></span><br class=form>
</div>

<p><button type=button id=step1btn style="display:none">Continue</button></p>

<div id=step2 style="display:none">
    <h2>Step 2/3: Verification</h2>

    <p>To verify you are an instructor, you will need to provide one of the following:</p>
    <ol>
        <li>A school website that lists you as a teacher. This could be a 
            school directory, a class schedule, a department website, or a 
            faculty website.</li>
        <li>An email from a supervisor, colleague, or school HR verifying you are a teacher. 
            Have that person send the email to 
            <a href="mailto:support@myopenmath.com">support@myopenmath.com</a>.  
            The person sending the email must be listed on a school website.</li>
        <li>Upload a picture of a school ID indicating you are a teacher</li>
    </ol>

    <span class=form><label for=vertype>What method would you like to use?</label></span>
    <span class=formright><select id=vertype>
        <option value="">Select...</option>
        <option value="url">Provide a website</option>
        <option value="email">Send an email</option>
        <option value="upload">Upload a school ID</option>
        </select></span><br class=form>
    
    <div id=verurlwrap class=vertypes style="display:none">
        <span class=form><label for=verurl>Website URL:</label></span>
        <span class=formright><input name=verurl id=verurl size=40 /></span><br class=form>
    </div>
    <div id=veremailwrap class=vertypes style="display:none">
        <span class=form><label for=veremail>The person we should expect an email from:</label></span>
        <span class=formright><input name=veremail id=veremail size=40 /></span><br class=form>
    </div>
    <div id=veruploadwrap class=vertypes style="display:none">
        <span class=form><label for=verupload>Picture of school ID:</label></span>
        <span class=formright><input type=file name=verupload id=verupload accept=".jpg,.pdf,.jpeg,.gif,.png"/></span><br class=form>
    </div>

    <p><button type=button id=step2btn style="display:none">Continue</button></p>
</div>

<div id=step3 style="display:none">
    <h2>Step 3/3: Account Details</h2>

    <span class=form><label for=firstname>Given Name:</label></span>
    <span class=formright>
        <input name=firstname autocomplete="given-name" size=40 
        value="<?php echo Sanitize::encodeStringForDisplay($firstname);?>"/>
    </span><br class=form>

    <span class=form><label for=lstname>Family Name:</label></span>
    <span class=formright>
        <input name=lastname autocomplete="family-name" size=40 
            value="<?php echo Sanitize::encodeStringForDisplay($lastname);?>" />
    </span><br class=form>

    <span class=form><label for=email>Email:</label>
        <span id=emailwarn style="display:none">This email <em>must</em> be the one listed 
        on the website provided, or be an official college email address or your request 
        <em>will</em> be denied.</span>
    </span>
    <span class=formright>
        <input name=email autocomplete="email" size=40
        value="<?php echo Sanitize::encodeStringForDisplay($email);?>" />
    </span><br class=form>

    <span class=form><label for=SID>Username:</label></span>
    <span class=formright>
        <input name=SID value="<?php echo Sanitize::encodeStringForDisplay($SID);?>" size=40 />
    </span><br class=form>

    <span class=form><label for=pw1>Password:</label></span>
    <span class=formright><input type=password name=pw1 size=40 /></span><br class=form>

    <span class=form><label for=pw2>Reenter Password:</label></span>
    <span class=formright><input type=password name=pw2 size=40 /></span><br class=form>

    <span class=form><input type=checkbox name=agree id=agree>
        <label for="agree">I have read and agree to the 
        <a href="#" onclick="GB_show('Terms of Use','<?php echo $CFG['GEN']['TOSpage'];?>',700,500);return false;">
        Terms of Use</a></label></span><br class=form />

    <p><button type=submit id=step3btn disabled>Request Account</button></p>
</div>

<span style="display:none"><input name=hval></span>
<input type=hidden name=challenge value="<?php echo Sanitize::encodeStringForDisplay($_SESSION['challenge']);?>"/>

</form>

<script type="text/javascript">
$(function() {
    $('#schooltype').on('change', function () {
        var val = this.value;
        if (val === '') {
            $('#locwrap').slideUp();
        } else {
            $('#locwrap').slideDown();
        }
        $('.locdesc').hide();
        $('.'+val+'src').slideDown();
    });
    $('#schoolloc').on('change', function () {
        var val = this.value;
        $('.selopt').hide();
        $('#'+val+'sel').slideDown();
    });
    $('#searchterms').on('input', function () {
        $('#searchresultwrapper').hide();
    });
    $('#dosearch').on('click', function () {
        ipedssearch({
            type: 'name',
            ipedtypefield: 'schooltype',
            searchfield: 'searchterms',
            resultfield: 'ipeds',
            wrapper: 'searchresultwrapper',
            includeselect: true
        });
    });

    $('#country').on('change', function () {
        var country = this.value;
        if (country != '') {
            ipedssearch({
                type: 'country',
                searchfield: 'country',
                resultfield: 'intlipeds',
                wrapper: 'intlwrap',
                includeselect: true
            });
        }
    });

    $("#ipeds,#intlipeds").on('change', function () {
        var val = this.value;
        if (val == '0') {
            $('#otherschool').slideDown();
            $('#step1btn').show();
        } else {
            $('#otherschool').slideUp();
            if (val != '') {
                $('#step2').show().get(0).scrollIntoView();
            }
        }
        
    });

    $('#step1btn').on('click', function() {
        $(this).parent().hide();
        $('#step2').show().get(0).scrollIntoView();
    });
    $('#vertype').on('change', function() {
        $('.vertypes').hide();
        var val = this.value;
        $('#ver'+val+'wrap').slideDown();
    });
    $("#verurl,#veremail,#verupload").on('input change', function () {
        $('#step2btn').show();
    });
    $('#step2btn').on('click', function() {
        $(this).parent().hide();
        $('#step3').show().get(0).scrollIntoView();
    })
    $('#agree').on('click change', function () {
        $('#step3btn').prop('disabled', this.checked===false);
    });

})
</script>
<?php 
$extrarequired = array('agree', 'schooltype', 'schooloc',
    'ipeds', 'intlipeds', 'vertype', 'verurl','veremail', 'verupload');

$requiredrules = array(
    'ipeds' => 'function(){return document.getElementById("schoolloc").value == "us";}',
    'intlipeds' => 'function(){return document.getElementById("schoolloc").value == "intl";}',
    'verurl' => 'function(){return document.getElementById("vertype").value == "url";}',
    'veremail' => 'function(){return document.getElementById("vertype").value == "email";}',
    'verupload' => 'function(){return document.getElementById("vertype").value == "upload";}',
);

showNewUserValidation('newinstrform', $extrarequired, $requiredrules);

require('footer.php');