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
    </style>


  </head>

  <body bgcolor="black">
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
    	{ stimulus: "<p class='RED'   >XXXXXXXX</p>",    is_html: true, data: { stimulus_type: "red" }, timing_response: 5000 },
        { stimulus: "<p class='GREEN'   >XXXXXXXX</p>",  is_html: true, data: { stimulus_type: "green" }, timing_response: 5000 },	  
        { stimulus: "<p class='BLUE'   >XXXXXXXX</p>",   is_html: true, data: { stimulus_type: "blue" }, timing_response: 5000 },	  
        { stimulus: "<p class='YELLOW'   >XXXXXXXX</p>", is_html: true, data: { stimulus_type: "yellow" }, timing_response: 5000 } ];	  

    var all_test_trials = jsPsych.randomization.repeat(test_stimuli, 10);

    // Experiment Instructions
    var welcome_message = "<h1>ABCD's Stroop Test</h1>";

    var instructions = "<div id='instructions'><p>You will see a " +
	"series of images that look similar to this:</p><p>" +
	"<p class='RED'>XXXXXXXX</p><p>Press the color " +
	"key that corresponds to the color (r-red, g-green, b-blue, y-yellow)." +
	" For example you would press 'r' on the keyboard for this image. Press enter to start.</p>";

    var debrief = "<div id='instructions'><p>Thank you for " +
	  "participating! Press enter to see the data.</p></div>";

    var test_block = {
    	type: 'single-stim',
	choices: ['b', 'y', 'r', 'g'],
	timing_post_trial: post_trial_gap,
	timeline: all_test_trials,
	on_finish: function(data) {
		jsPsych.data.addDataToLastTrial({is_test_element: true});
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
    timeline.push( { type: 'text', text: debrief } );

    jsPsych.init({
       timeline: timeline,
       on_finish: function(data) {
	      // call from tutorial displays JSON string as final page
   	      // jsPsych.data.displayData();

	      jQuery.post('code/php/events.php',
		{ "data": JSON.stringify(jsPsych.data.getData()), "date": moment().format() }, function(data) {
                  // did it work?
                  console.log(data);
		  if (data.ok == 0) {
		     alert('Error: ' + data.message);
		  }
                  // export now
                  exportToCsv("Stroop-Task_" + Site + "_" + SubjectID + "_" + Session + "_" + moment().format() + ".csv",
		  			     jsPsych.data.getData());
	      });


       }
    });
    
</script>
</html>
    
