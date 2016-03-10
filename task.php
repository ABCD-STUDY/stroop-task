<!doctype html>
<html>

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


  <head>
    <title>Stroop Task</title>
    <meta charset="utf-8" />
    <!-- Load jQuery -->
    <script src="js/jquery.min.js"></script>
    <script src='js/moment.min.js'></script>
   
    <!-- Load the jspsych library and plugins -->
    <script src="js/jspsych/jspsych.js"></script>
    <script src="js/jspsych/plugins/jspsych-text.js"></script>
    <script src="js/jspsych/plugins/jspsych-single-stim.js"></script>
    <script src='https://cdn.plot.ly/plotly-latest.min.js'></script>
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
h2 {
   color: #ffffff;
   font-family: 'Lato', sans-serif;
   font-size: 34px;
   font-weight: 300;
   line-height: 48px;
   margin: 0 0 48px;
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

// write a page with the stats calculated from the data
function createStats( data ) {
    var con = [];
    var incon = [];
    // focus data

    var numConCorrect = 0;
    var numInConCorrect = 0;
    var totalCon = 0;
    var totalInCon = 0;
    for (var i = 0; i < data.length; i++) {
	if (typeof data[i].is_real_element != 'undefined' && data[i].is_real_element == true && data[i].key_press != -1) {
            if (data[i].stimulus_type == "congruent") {
		con.push(data[i].rt);
		totalCon++;
		if (data[i].correct == true)
		  numConCorrect++;
	    }
 	    if (data[i].stimulus_type == "incongruent") {
		incon.push(data[i].rt);
		totalInCon++;
		if (data[i].correct == true)
		  numInConCorrect++;
	    }
	}
    }
    // create stats
    mincon   = con.reduce(function(a, b) { return (b < a)?b:a; });
    maxcon   = con.reduce(function(a, b) { return (b > a)?b:a; });
    minincon = incon.reduce(function(a, b) { return (b < a)?b:a; });
    maxincon = incon.reduce(function(a, b) { return (b > a)?b:a; });

    tmin = (mincon < minincon)?mincon:minincon;
    tmax = (maxcon > maxincon)?maxcon:maxincon;					 
			
    // we would like to get a histogram of reaction times (not the once that are -1)
    // for the congruent and the incongruent tasks
    var histCong = new Array(5).fill(0);
    var space = (maxcon-mincon) / (histCong.length-1);			
    con.map(function(a) { histCong[ Math.round( (a-mincon)/(maxcon-mincon) * (histCong.length-1)  ) ]++; });
    var sumcon = histCong.reduce(function(a, b) { return a+(b*space); });
    histCong = histCong.map(function(a) { return a/sumcon; });
	
    var histInCong = new Array(5).fill(0);
    incon.map(function(a) { histInCong[ Math.round( (a-minincon)/(maxincon-minincon) * (histInCong.length-1)  ) ]++; });
    space = (maxincon-minincon) / (histInCong.length-1);			
    var sumincon = histInCong.reduce(function(a, b) { return a+(b*space); });
    histInCong = histInCong.map(function(a) { return a/sumincon; });
    
    
    // we also like to have the mean and variance for both
    var meancon = con.reduce( function (a, b) { return a+b; })/con.length;
    var meanincon = incon.reduce( function (a, b) { return a+b; })/incon.length;
    var varcon = con.map( function (a) { return (a-meancon) * (a-meancon); }).reduce(function(a,b) { return a+b; }) /(con.length - 1);
    var stdcon = Math.sqrt(varcon);
    var varincon = incon.map( function (a) { return (a-meanincon) * (a-meanincon); }).reduce(function(a,b) { return a+b; }) /(incon.length - 1)
    var stdincon = Math.sqrt(varincon);
    var curveCon = [ new Array(100).fill(0), new Array(100).fill(0) ];
    curveCon[0] = curveCon[0].map(function(_, i) { return tmin + i * (tmax-tmin)/(100-1);  });
    curveCon[1] = curveCon[0].map(function(a,i) { return 1.0/(stdcon * Math.sqrt(2.0*3.1415927)) * Math.exp( - (a-meancon)*(a-meancon)/(2.0*stdcon*stdcon)) ; });
    space = (tmax-tmin) / (100-1);
    var sum2 = curveCon[1].reduce(function(a,b) { return a+(b*space); });
    curveCon[1] = curveCon[1].map(function(a,i) { return a/sum2; });			
			
    var curveInCon = [ new Array(100).fill(0), new Array(100).fill(0) ];
    curveInCon[0] = curveInCon[0].map(function(_, i) { return tmin + i * (tmax-tmin)/(100-1);  });
    curveInCon[1] = curveInCon[0].map(function(a,i) { return 1.0/(stdincon * Math.sqrt(2.0*3.1415927)) * Math.exp( - (a-meanincon)*(a-meanincon)/(2.0*stdincon*stdincon)) ; });
    space = (tmax-tmin) / (100-1);			
    sum2 = curveInCon[1].reduce(function(a,b) { return a+(b*space); });
    curveInCon[1] = curveInCon[1].map(function(a,i) { return a/sum2; });			

    // write the page to w using data in data
    str = "\<h2 style='margin-top: 30px; margin-left: 40px;'\>"+ SubjectID +", "+ Session +"\</h2\>";
    str = str + "\<div id='instructions'\>\<p\>Thank you for participating!\</p\>\</div\>";
    str = str + "\<div id='histogram'\>\</div\>\<div style='margin-left: 40px;'\>";
    str = str + "\<p\>\<div\>mean reaction time (congruent): " + Math.round(meancon,0) +"ms (&#177;" + Math.round(stdcon,2) + ")\</div\>";
    str = str + "\<div\>mean reaction time (in-congruent): "+Math.round(meanincon,0)+"ms (&#177;"+ Math.round(stdincon,2) +")\</div\>";
    str = str + "\<div\>congruent answers (correct/total): " + numConCorrect + "/" + totalCon + "\</div\>";
    str = str + "\<div\>in-congruent answers (correct/total): " + numInConCorrect + "/" + totalInCon + "\</div\>";
    str = str + "\</p\>\<div\>";

    // we have the placeholder for plotly in the string, look for it after the page is on to add the plot itself
    setTimeout(function () {
	var con  = {
      	    marker: {
	  	color: 'rgb(0,100,80)'
  	    },
	    name: 'congruent',
	    x: histCong.map(function(a, i) { return i*(maxcon-mincon)/(histCong.length-1) + mincon; }),
	    y: histCong,
	    type: 'bar'
	};
	var incon  = {
	    marker: {
		color: 'rgb(176,0,41)'
	    },
	    name: 'in-congruent',
	    x: histInCong.map(function(a, i) { return i*(maxincon-minincon)/(histInCong.length-1) + minincon; }),
            y: histInCong,
	    type: 'bar'
	};
	var curvecon = {
	    line: {
		color: 'rgb(0,100,80)'
	    },
	    name: 'fit congruent',
	    x: curveCon[0],
	    y: curveCon[1],
	    type: 'scatter'
	};
	var curveincon = {
	    line: {
		color: 'rgb(176,0,41)'
	    },
	    name: 'fit in-congruent',
	    x: curveInCon[0],
	    y: curveInCon[1],
	    type: 'scatter'
	};
	var data = [ con, incon, curvecon, curveincon ];
        var layout = {
	    autosize: true,
	    paper_bgcolor: '#292929',
	    plot_bgcolor: '#292929',
	    xaxis: {
		tickcolor: '#fff',
   	        titlefont: { color: '#fff' },
   	        tickfont: { color: '#fff' },
		linecolor: '#fff'
	    },
	    yaxis: {
		tickcolor: '#fff',
   	        titlefont: { color: '#fff' },
   	        tickfont: { color: '#fff' },
		linecolor: '#fff'
	    },
	    legend: {
		font: { color: '#fff' }
	    }
	};
	
	Plotly.newPlot('histogram', data, layout);
    }, 500); // we wait for the elements to be written to the page before we call plotly with the id of the field
    return str;
}


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
    // timeline.push( test_block );
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

    timeline.push( { type: 'text',
    		     text: function() {
   			return createStats( jsPsych.data.getData() );
		     }		     
    });

    jsPsych.init({
       timeline: timeline,
       on_finish: function(data) {
	      jQuery.post('code/php/events.php',
		{ "data": JSON.stringify(jsPsych.data.getData()), "date": moment().format() }, function(data) {
		  if (typeof data.ok == 'undefined' || data.ok == 0) {
		   //  alert('Error: ' + data.message);
		  }
                  // export as csv for download on client
                  exportToCsv("Stroop-Task_" + Site + "_" + SubjectID + "_" + Session + "_" + moment().format() + ".csv",
		  			     jsPsych.data.getData());
	      });
	      
       }
    });
    
</script>
</html>
    
