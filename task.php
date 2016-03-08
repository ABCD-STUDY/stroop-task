<?php
  session_start();

  include($_SERVER["DOCUMENT_ROOT"]."/code/php/AC.php");
  $user_name = check_logged(); /// function checks if visitor is logged.
  $admin = false;

  if ($user_name == "") {
    // user is not logged in

  } else {
    $admin = true;
    echo('<script type="text/javascript"> user_name = "'.$user_name.'"; </script>'."\n");
    echo('<script type="text/javascript"> admin = '.($admin?"true":"false").'; </script>'."\n");
  }

  $subjid = "";
  $sessionid = "";
  if( isset($_SESSION['ABCD']) && isset($_SESSION['ABCD']['stroop']) ) {
     if (isset($_SESSION['ABCD']['stroop']['subjid'])) {
        $subjid  = $_SESSION['ABCD']['stroop']['subjid'];
     }
     if (isset($_SESSION['ABCD']['stroop']['sessionid'])) {
        $sessionid  = $_SESSION['ABCD']['stroop']['sessionid'];
     }
  }
  echo('<script type="text/javascript"> SubjectID = "'.$subjid.'"; </script>'."\n");
  echo('<script type="text/javascript"> Session = "'.$sessionid.'"; </script>'."\n");

   $permissions = list_permissions_for_user( $user_name );

   $site = "";
   foreach ($permissions as $per) {
     $a = explode("Site", $per); // permissions should be structured as "Site<site name>"

     if (count($a) > 0) {
        $site = $a[1];
	break;
     }
   }
   if ($site == "") {
     echo (json_encode ( array( "message" => "Error: no site assigned to this user" ) ) );
     return;
   }
   echo('<script type="text/javascript"> Site = "'.$site.'"; </script>'."\n");

?>

<!doctype html>
<html>

  <head>
    <title>Stroop Task</title>
    <!-- Load jQuery -->
    <script src="js/jquery.min.js"></script>
    <script src='js/moment.min.js'></script>
   
    <!-- Load the jspsych library and plugins -->
    <script src="js/jspsych/jspsych.js"></script>
    <script src="js/jspsych/plugins/jspsych-text.js"></script>
    <script src="js/jspsych/plugins/jspsych-single-stim.js"></script>
    <!-- Load the stylesheet -->
    <!-- <link href="experiment.css" type="text/css" rel="stylesheet"></link> -->
    <link href="js/jspsych/css/jspsych.css" rel="stylesheet" type="text/css"></link>
    <link href='https://fonts.googleapis.com/css?family=Lato:300' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic' rel='stylesheet' type='text/css'>
<style>
body {
  backgroud-color: black;
  color: white;
}
.RED {
   color: red;
   text-align: center;
   font-size: 32pt;
   vertical-align: middle;
   line-height: 400px;
   font-weight: 900;
}
.GREEN {
   color: green;
   text-align: center;
   font-size: 32pt;
   vertical-align: middle;
   line-height: 400px;
   font-weight: 900;
}
.BLUE {
   color: blue;
   text-align: center;
   font-size: 32pt;
   vertical-align: middle;
   line-height: 400px;
   font-weight: 900;
}
.YELLOW {
   color: yellow;
   text-align: center;
   font-size: 32pt;
   vertical-align: middle;
   line-height: 400px;
   font-weight: 900;
}
h1 {
   color: #ffffff;
   font-family: 'Lato', sans-serif;
   font-size: 54px;
   font-weight: 300;
   line-height: 58px;
   margin: 0 0 58px;
   border-bottom: double #555;
   padding-bottom: 30px;
}
p {
   color: #adb7bd;
   font-family: 'Open Sans', Arial, sans-serif;
   font-size: 16px;
   line-height: 26px;
   text-indent: 30px;
   margin: 0;
}

a {
   color: #fe921f;
   text-decoration: underline;
}

a:hover { color: #ffffff }
.date {
      background: #fe921f;
      color: #ffffff;
      display: inline-block;
      font-family: 'Lato', sans-serif;
      font-size: 12px;
      font-weight: bold;
      line-height: 12px;
      letter-spacing: 1px;
      margin: 0 0 30px;
      padding: 10px 15px 8px;
      text-transform: uppercase;
}

.date2 { color: #bbc3c8; background: #292929; display: inline-block; font-family: 'Georgia', serif; font-style: italic; font-size: 18px; line-height: 22px; margin: 0 0 20px 18px; padding: 10px 12px 8px; position: absolute; bottom: -36px; }
    </style>


  </head>

  <body bgcolor="#292929">
    <div id="jspsych_target"></div>
  </body>

  <script>

function exportToCsv(filename, rows) {
    var k = { "SubjectID": 1, "Site": 1, "Session": 1 };
    for (var i = 0; i < rows.length; i++) {
       var k2 = Object.keys(rows[i]);
       for (var j = 0; j < k2.length; j++) {
          k[k2[j]] = 1;
       } 
    }
    k = Object.keys(k);

    var csvFile = k.join(",") + "\n";
    for (var i = 0; i < rows.length; i++) {
       rows[i]['SubjectID'] = SubjectID;
       rows[i]['Site'] = Site;
       rows[i]['Session'] = Session;
       csvFile += k.map(function(a) { return rows[i][a] }).join(",") + "\n";
    }
    
    var blob = new Blob([csvFile], { type: 'text/csv;charset=utf-8;' });
    if (navigator.msSaveBlob) { // IE 10+
	navigator.msSaveBlob(blob, filename);
    } else {
	var link = document.createElement("a");
	if (link.download !== undefined) { // feature detection
	    // Browsers that support HTML5 download attribute
	    var url = URL.createObjectURL(blob);
	    link.setAttribute("href", url);
	    link.setAttribute("download", filename);
	    link.style.visibility = 'hidden';
	    document.body.appendChild(link);
	    link.click();
	    document.body.removeChild(link);
	}
    }
}



    var post_trial_gap = function() {
        return Math.floor( Math.random() * 1000 ) + 500;
    }

    var test_stimuli = [
    	{ stimulus: "<p class='RED'   >XXXXXXXX</p>",    is_html: true, data: { stimulus_type: "red", correct_color: 'RED' }, timing_response: 5000 },
        { stimulus: "<p class='GREEN'   >XXXXXXXX</p>",  is_html: true, data: { stimulus_type: "green", correct_color: 'GREEN' }, timing_response: 5000 },
        { stimulus: "<p class='BLUE'   >XXXXXXXX</p>",   is_html: true, data: { stimulus_type: "blue", correct_color: 'BLUE' }, timing_response: 5000 },
        { stimulus: "<p class='YELLOW'   >XXXXXXXX</p>", is_html: true, data: { stimulus_type: "yellow", correct_color: 'YELLOW' }, timing_response: 5000 } ];

    // training is long (20 is 20*4 stimulus presentations), test is short
    var all_test_trials = jsPsych.randomization.repeat(test_stimuli, 5);

    // Experiment Instructions
    var welcome_message = "<div class='date'>Adolescent Brain Cognitive Development</div><div style='position: relative;'><h1>ABCD's Stroop Test</h1><div class='date2'>March 2016</div></div><p>Naming the color of a word printed in different inks poses a problem if the word itself denotes a color. The work \"<span style='color: lightgreen;'>red</span>\" written with green ink can be more easily read than its color can be named. This creates a reaction time delay that can be measured using the Stroop test. After an initial training phase this application will try to measure the delay between naming correctly or incorrectly colored words.</p><br/><p>Source code for this assessment has been created using jsPsych and can be viewed on <a href='https://github.com/ABCD-STUDY/stroop'>github</a>.</p>";

    var instructions = "<div id='instructions'><p>You will see a " +
	"series of images that look similar to this:</p><p>" +
	"<p class='RED'>XXXXXXXX</p><p>Press the color " +
	"key that corresponds to the color (r-red, g-green, b-blue, y-yellow)." +
	" For example you would press 'r' on the keyboard for this image. Press enter to start.</p>";

    var debrief = "<div id='instructions'><p>Thank you for " +
	  "participating! Press enter to see the data.</p></div>";

    var startreal = "<div id='instructions'><p>Now the same with words. " +
	  "Press enter to see the data.</p></div>";

    var test_block = {
    	type: 'single-stim',
	choices: ['b', 'y', 'r', 'g'],
	timing_post_trial: post_trial_gap,
	timeline: all_test_trials,
	on_finish: function(data) {
		jsPsych.data.addDataToLastTrial({is_real_element: false});
	    	var correct = false;
	   	if(data.stimulus_type == 'red' && data.key_press == 82){
	      		correct = true;
	   	} else if(data.stimulus_type == 'green' && data.key_press == 71){
	      		correct = true;
	  	} else if(data.stimulus_type == 'blue' && data.key_press == 66) {
		        correct = true;
		} else if(data.stimulus_type == 'yellow' && data.key_press == 89) {
		        correct = true;
		}
	   	jsPsych.data.addDataToLastTrial({correct: correct});
	}
    };

    var timeline = [];
    timeline.push( { type: 'text', text: welcome_message } );
    timeline.push( { type: 'text', text: instructions } );
    timeline.push( test_block );
    timeline.push( { type: 'text', text: startreal } );
    
    var real_stimuli = [
	{ stimulus: "<p class='RED'   >RED</p>",    is_html: true, data: { stimulus_type: "congruent", correct_color: 'RED' }, timing_response: 5000 },
	{ stimulus: "<p class='GREEN'   >GREEN</p>",  is_html: true, data: { stimulus_type: "congruent", correct_color: 'GREEN' }, timing_response: 5000 },
	{ stimulus: "<p class='BLUE'   >BLUE</p>",   is_html: true, data: { stimulus_type: "congruent", correct_color: 'BLUE' }, timing_response: 5000 },
	{ stimulus: "<p class='YELLOW'   >YELLOW</p>", is_html: true, data: { stimulus_type: "congruent", correct_color: 'YELLOW' }, timing_response: 5000 },

	{ stimulus: "<p class='RED'   >RED</p>",    is_html: true, data: { stimulus_type: "congruent", correct_color: 'RED' }, timing_response: 5000 },
	{ stimulus: "<p class='GREEN'   >GREEN</p>",  is_html: true, data: { stimulus_type: "congruent", correct_color: 'GREEN' }, timing_response: 5000 },
	{ stimulus: "<p class='BLUE'   >BLUE</p>",   is_html: true, data: { stimulus_type: "congruent", correct_color: 'BLUE' }, timing_response: 5000 },
	{ stimulus: "<p class='YELLOW'   >YELLOW</p>", is_html: true, data: { stimulus_type: "congruent", correct_color: 'YELLOW' }, timing_response: 5000 },

	{ stimulus: "<p class='RED'   >RED</p>",    is_html: true, data: { stimulus_type: "congruent", correct_color: 'RED' }, timing_response: 5000 },
	{ stimulus: "<p class='GREEN'   >GREEN</p>",  is_html: true, data: { stimulus_type: "congruent",correct_color: 'GREEN' }, timing_response: 5000 },
	{ stimulus: "<p class='BLUE'   >BLUE</p>",   is_html: true, data: { stimulus_type: "congruent", correct_color: 'BLUE' }, timing_response: 5000 },
	{ stimulus: "<p class='YELLOW'   >YELLOW</p>", is_html: true, data: { stimulus_type: "congruent", correct_color: 'YELLOW' }, timing_response: 5000 },

	{ stimulus: "<p class='RED'   >GREEN</p>",    is_html: true, data: { stimulus_type: "incongruent", correct_color: 'RED' }, timing_response: 5000 },
	{ stimulus: "<p class='BLUE'   >GREEN</p>",   is_html: true, data: { stimulus_type: "incongruent", correct_color: 'BLUE' }, timing_response: 5000 },
	{ stimulus: "<p class='YELLOW'   >GREEN</p>", is_html: true, data: { stimulus_type: "incongruent", correct_color: 'YELLOW' }, timing_response: 5000 },

	{ stimulus: "<p class='GREEN'  >RED</p>",    is_html: true, data: { stimulus_type: "incongruent", correct_color: 'GREEN' }, timing_response: 5000 },
	{ stimulus: "<p class='BLUE'   >RED</p>",   is_html: true, data: { stimulus_type: "incongruent", correct_color: 'BLUE' }, timing_response: 5000 },
	{ stimulus: "<p class='YELLOW'   >RED</p>", is_html: true, data: { stimulus_type: "incongruent", correct_color: 'YELLOW' }, timing_response: 5000 },

	{ stimulus: "<p class='GREEN'  >BLUE</p>",    is_html: true, data: { stimulus_type: "incongruent", correct_color: 'GREEN' }, timing_response: 5000 },
	{ stimulus: "<p class='RED'   >BLUE</p>",   is_html: true, data: { stimulus_type: "incongruent", correct_color: 'RED' }, timing_response: 5000 },
	{ stimulus: "<p class='YELLOW'   >BLUE</p>", is_html: true, data: { stimulus_type: "incongruent", correct_color: 'YELLOW' }, timing_response: 5000 },
	
	{ stimulus: "<p class='GREEN'  >YELLOW</p>",    is_html: true, data: { stimulus_type: "incongruent", correct_color: 'GREEN' }, timing_response: 5000 },
	{ stimulus: "<p class='RED'   >YELLOW</p>",   is_html: true, data: { stimulus_type: "incongruent", correct_color: 'RED' }, timing_response: 5000 },
	{ stimulus: "<p class='BLUE'   >YELLOW</p>", is_html: true, data: { stimulus_type: "incongruent", correct_color: 'BLUE' }, timing_response: 5000 }
    ];

    // show the same number of congruent and incongruent tasks, each incongruent tasks is displayed twice
    var all_real_trials = jsPsych.randomization.repeat(real_stimuli, 2);

    var real_block = {
    	type: 'single-stim',
	choices: ['b', 'y', 'r', 'g'],
	timing_post_trial: post_trial_gap,
	timeline: all_real_trials,
	on_finish: function(data) {
		jsPsych.data.addDataToLastTrial({is_real_element: true});
	    	var correct = false;
	   	if(data.correct_color == 'RED' && data.key_press == 82){
	      		correct = true;
	   	} else if(data.correct_color == 'GREEN' && data.key_press == 71){
	      		correct = true;
	  	} else if(data.correct_color == 'BLUE' && data.key_press == 66) {
		        correct = true;
		} else if(data.correct_color == 'YELLOW' && data.key_press == 89) {
		        correct = true;
		}
	   	jsPsych.data.addDataToLastTrial({correct: correct});
	}
    };

    timeline.push( real_block );
    timeline.push( { type: 'text', text: debrief } );

    jsPsych.init({
       timeline: timeline,
       on_finish: function(data) {
	      jQuery.post('code/php/events.php',
		{ "data": JSON.stringify(jsPsych.data.getData()), "date": moment().format() }, function(data) {
		  if (typeof data.ok == 'undefined' || data.ok == 0) {
		     alert('Error: ' + data.message);
		  }
                  // export as csv for download on client
                  exportToCsv("Stroop-Task_" + Site + "_" + SubjectID + "_" + Session + "_" + moment().format() + ".csv",
		  			     jsPsych.data.getData());
	      });
       }
    });
    
</script>
</html>
    
