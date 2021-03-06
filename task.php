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
  $run = ""; 
  if( isset($_SESSION['ABCD']) && isset($_SESSION['ABCD']['stroop']) ) {
     if (isset($_SESSION['ABCD']['stroop']['subjid'])) {
        $subjid  = $_SESSION['ABCD']['stroop']['subjid'];
     }
     if (isset($_SESSION['ABCD']['stroop']['sessionid'])) {
        $sessionid  = $_SESSION['ABCD']['stroop']['sessionid'];
     }
     if (isset($_SESSION['ABCD']['stroop']['run'])) {
        $run  = $_SESSION['ABCD']['stroop']['run'];
     }
  }
  echo('<script type="text/javascript"> SubjectID = "'.$subjid.'"; </script>'."\n");
  echo('<script type="text/javascript"> Session = "'.$sessionid.'"; </script>'."\n");
  echo('<script type="text/javascript"> Run = "'.$run.'"; </script>'."\n");

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
    <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
    <script src="/js/jquery.ui.touch-punch.min.js"></script>
    <script src="js/moment.min.js"></script>
   
    <!-- Load the jspsych library and plugins -->
    <script src="js/jspsych/jspsych.js"></script>
    <script src="js/jspsych/plugins/jspsych-text.js"></script>
    <script src="js/jspsych/plugins/jspsych-single-stim.js"></script>
    <script src="js/jspsych/plugins/jspsych-button-response.js"></script>
    <script src="js/plotly-latest.min.js"></script>
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
   font-size: 72pt;
   vertical-align: middle;
   line-height: 400px;
   font-weight: 900;
}
.GREEN {
   color: rgb(0,250,0);
   text-align: center;
   font-size: 72pt;
   vertical-align: middle;
   line-height: 400px;
   font-weight: 900;
}
.BLUE {
   color: blue;
   text-align: center;
   font-size: 72pt;
   vertical-align: middle;
   line-height: 400px;
   font-weight: 900;
}
.YELLOW {
   color: yellow;
   text-align: center;
   font-size: 72pt;
   vertical-align: middle;
   line-height: 400px;
   font-weight: 900;
}
.GRAY {
   color: gray;
   text-align: center;
   font-size: 72pt;
   vertical-align: middle;
   line-height: 400px;
   font-weight: 900;
}
h1 {
   color: #ffffff;
   font-family: 'Lato', sans-serif;
   font-size: 72px;
   font-weight: 300;
   line-height: 72px;
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
   font-size: 36px;
   line-height: 48px;
   text-indent: 48px;
   margin: 0;
}

a {
   color: #fe921f;
   text-decoration: underline;
}

a:hover { color: #292929; }
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
.jspsych-btn {
  border: 1px solid #292929;
  margin-right: 0px;
  border-radius: 80px;
  width: 160px;
  height: 160px;
  position: absolute;
  bottom: 40px;
  background-color:#292929;
}
.jspsych-btn:hover {
  background-color: #292929;
  border: 1px solid #292929;
}
.red {
  color: red;
}
.yellow {
  color: yellow;
}
.green {
  color: rgb(0,250,0);
}
.blue {
 color: blue;
}
.inner_circle {
  height: 80px;
  width: 80px;
  border-radius: 40px;
  border: 1px solid white;
  box-shadow: 0px 0px 5px white;
  margin-right: 20px;
}
#jspsych-button-response-button-0 {
  position: absolute;
  color: red;
  left: 10%;
}
#jspsych-button-response-button-1 {
  position: absolute;
  color: yellow;
  left: 31%;
}
#jspsych-button-response-button-2 {
  position: absolute;
  color:  rgb(0,250,0);
  right: 31%;
}
#jspsych-button-response-button-3 {
  position: absolute;
  color: blue;
  right: 10%;
}

</style>


  </head>

  <body bgcolor="#292929">
    <div id="jspsych_target"></div>
  </body>
  
  <script>

// write a page with the stats calculated from the data
function createStats( data ) {
    var con = [];
    var neut = [];
    // focus data

    var numConCorrect = 0;
    var numNeutCorrect = 0;
    var totalCon = 0;
    var totalNeut = 0;
    for (var i = 0; i < data.length; i++) {
	if (typeof data[i].is_real_element != 'undefined' && data[i].is_real_element == true && data[i].button_pressed != -1) {
            if (data[i].stimulus_type == "inc") {
		con.push(data[i].rt);
		totalCon++;
		if (data[i].correct == true)
		  numConCorrect++;
	    }
 	    if (data[i].stimulus_type == "neut") {
		neut.push(data[i].rt);
		totalNeut++;
		if (data[i].correct == true)
		  numNeutCorrect++;
	    }
	}
    }
    // create stats (will not work is con or neut are empty)
    mincon   = con.reduce(function(a, b) { return (b < a)?b:a; });
    maxcon   = con.reduce(function(a, b) { return (b > a)?b:a; });
    minneut = neut.reduce(function(a, b) { return (b < a)?b:a; });
    maxneut = neut.reduce(function(a, b) { return (b > a)?b:a; });

    tmin = (mincon < minneut)?mincon:minneut;
    tmax = (maxcon > maxneut)?maxcon:maxneut;					 
			
    // we would like to get a histogram of reaction times (not the once that are -1)
    // for the congruent and the incongruent tasks
    var histCong = new Array(5).fill(0);
    var space = (maxcon-mincon) / (histCong.length-1);			
    con.map(function(a) { histCong[ Math.round( (a-mincon)/(maxcon-mincon) * (histCong.length-1)  ) ]++; });
    var sumcon = histCong.reduce(function(a, b) { return a+(b*space); });
    histCong = histCong.map(function(a) { return a/sumcon; });
	
    var histNeutg = new Array(5).fill(0);
    neut.map(function(a) { histNeutg[ Math.round( (a-minneut)/(maxneut-minneut) * (histNeutg.length-1)  ) ]++; });
    space = (maxneut-minneut) / (histNeutg.length-1);			
    var sumneut = histNeutg.reduce(function(a, b) { return a+(b*space); });
    histNeutg = histNeutg.map(function(a) { return a/sumneut; });
    
    // we also like to have the mean and variance for both
    var meancon = con.reduce( function (a, b) { return a+b; })/con.length;
    var meanneut = neut.reduce( function (a, b) { return a+b; })/neut.length;
    var varcon = con.map( function (a) { return (a-meancon) * (a-meancon); }).reduce(function(a,b) { return a+b; }) /(con.length - 1);
    var stdcon = Math.sqrt(varcon);
    var varneut = neut.map( function (a) { return (a-meanneut) * (a-meanneut); }).reduce(function(a,b) { return a+b; }) /(neut.length - 1)
    var stdneut = Math.sqrt(varneut);
    var curveCon = [ new Array(100).fill(0), new Array(100).fill(0) ];
    curveCon[0] = curveCon[0].map(function(_, i) { return tmin + i * (tmax-tmin)/(100-1);  });
    curveCon[1] = curveCon[0].map(function(a,i) { return 1.0/(stdcon * Math.sqrt(2.0*3.1415927)) * Math.exp( - (a-meancon)*(a-meancon)/(2.0*stdcon*stdcon)) ; });
    space = (tmax-tmin) / (100-1);
    var sum2 = curveCon[1].reduce(function(a,b) { return a+(b*space); });
    curveCon[1] = curveCon[1].map(function(a,i) { return a/sum2; });			
			
    var curveNeut = [ new Array(100).fill(0), new Array(100).fill(0) ];
    curveNeut[0] = curveNeut[0].map(function(_, i) { return tmin + i * (tmax-tmin)/(100-1);  });
    curveNeut[1] = curveNeut[0].map(function(a,i) { return 1.0/(stdneut * Math.sqrt(2.0*3.1415927)) * Math.exp( - (a-meanneut)*(a-meanneut)/(2.0*stdneut*stdneut)) ; });
    space = (tmax-tmin) / (100-1);			
    sum2 = curveNeut[1].reduce(function(a,b) { return a+(b*space); });
    curveNeut[1] = curveNeut[1].map(function(a,i) { return a/sum2; });			

    // write the page to w using data in data
    str = "\<h2 style='margin-top: 30px; margin-left: 40px;'\>"+ SubjectID +", "+ Session +"\</h2\>";
    str = str + "\<div id='instructions'\>\<p\>Thank you for participating!\</p\>\</div\>";
    str = str + "\<div id='histogram'\>\</div\>\<div style='margin-left: 40px;'\>";
    str = str + "\<p\>\<div\>mean reaction time (in-congruent): " + Math.round(meancon,0) +"msec (&#177;" + Math.round(stdcon,2) + "SD)\</div\>";
    str = str + "\<div\>mean reaction time (neutral): "+Math.round(meanneut,0)+"msec (&#177;"+ Math.round(stdneut,2) +"SD)\</div\>";
    str = str + "\<div\>in-congruent answers (correct/total): " + numConCorrect + "/" + totalCon + "\</div\>";
    str = str + "\<div\>neutral answers (correct/total): " + numNeutCorrect + "/" + totalNeut + "\</div\>";
    str = str + "\</p\>\<div\>";

    // we have the placeholder for plotly in the string, look for it after the page is on to add the plot itself
    setTimeout(function () {
	var con  = {
      	    marker: {
	  	color: 'rgb(0,100,80)'
  	    },
	    name: 'in-congruent',
	    x: histCong.map(function(a, i) { return i*(maxcon-mincon)/(histCong.length-1) + mincon; }),
	    y: histCong,
	    type: 'bar'
	};
	var neut  = {
	    marker: {
		color: 'rgb(176,0,41)'
	    },
	    name: 'neutral',
	    x: histNeutg.map(function(a, i) { return i*(maxneut-minneut)/(histNeutg.length-1) + minneut; }),
            y: histNeutg,
	    type: 'bar'
	};
	var curvecon = {
	    line: {
		color: 'rgb(0,100,80)'
	    },
	    name: 'fit in-congruent',
	    x: curveCon[0],
	    y: curveCon[1],
	    type: 'scatter'
	};
	var curveneut = {
	    line: {
		color: 'rgb(176,0,41)'
	    },
	    name: 'fit neutral',
	    x: curveNeut[0],
	    y: curveNeut[1],
	    type: 'scatter'
	};
	var data = [ con, neut, curvecon, curveneut ];
        var layout = {
	    autosize: true,
	    paper_bgcolor: '#292929',
	    plot_bgcolor: '#292929',
 	    xaxis: {
	        title: 'reaction time (msec)',		
		tickcolor: '#fff',
   	        titlefont: { color: '#fff' },
   	        tickfont: { color: '#fff' },
		linecolor: '#fff'
	    },
	    yaxis: {
	        title: 'rel. probability',		
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
	{ stimulus: "<p class='YELLOW'   >XXXXXXXX</p>", is_html: true, data: { stimulus_type: "yellow", correct_color: 'YELLOW' }, timing_response: 5000 }
    ];

  // this trial is for correct/incorrect feedback in example trials
  var test_trial_feedback = { 
	  is_html: true,
    timing_response: 3000,
    timing_post_trial: 500,
	  stimulus: function() { 
		  var lasttrialdata = jsPsych.data.getLastTrialData();
	 	  if(lasttrialdata.rt == -1){
			  return lasttrialdata.stimulus + "<p class='GRAY'>Time Ran Out</p>";
		  }
		  if(lasttrialdata.correct == true){
			  return lasttrialdata.stimulus + "<center><img src='happy.svg'></center>";
		  }
		  else{
			  return lasttrialdata.stimulus + "<center><img src='sad.svg'></center>";
		  }
    }
  }
    // training length depends on the answers provided by the user, only if <N> correct answers have been provided in a row
    // we quit the test

    // training is long (20 is 20*4 stimulus presentations), test is short
    var maxtrial_nums = 20;
    var all_test_trials = jsPsych.randomization.repeat(test_stimuli, maxtrial_nums); // we will cut the test short if 10 in a row are done correctly
    var test_trials = [];
    for(var i = 0; i < maxtrial_nums; i++){
	test_trials.push(all_test_trials[i]);
	test_trials.push(test_trial_feedback);
    }
    // Introduction
    var start_instructions = "<div id='inst'><p><br/>In this task, you will press the button that matches the color of a word, while ignoring what the word says.<br/><br/>The possible color responses are:<p>Red<span style='margin-right: 30px;'></span>Yellow<span style='margin-right: 30px;'></span>Green<span style='margin-right: 30px;'></span>Blue<br/><br/>Use your two index fingers when responding.</p>";
			
    // Experiment Instructions
    var instructions = "<div id='instructions'><p>To get you started, we will let you practice. <br/><br/>Your job is to press the button that matches the color of the string of xxxxx's.<br/><br/>The color that goes with each button is shown below<br/><br/>Use your two index fingers when responding.<br/><br/></div>"+
    "<div style=\"display: flex;\"><div class= 'inner_circle'><center><div style='padding-top: 20px'><img src='letterR.png'></div></center></div>"+
    "<div class= 'inner_circle'><center><div style='padding-top: 20px'><img src='letterY.png'></div></center></div>"+
    "<div class= 'inner_circle'><center><div style='padding-top: 20px'><img src='letterG.png'></div></center></div>"+
    "<div class= 'inner_circle'><center><div style='padding-top: 20px'><img src='letterB.png'></div></center></div></div>"+
    "<div id='instructions'><br/><br/></div>";

    var startreal = "<div id='instructions'><p><br/>You will now start the real task.<br/><br/>Your job is to press the button that matches the color of the word while ignoring what the word says. <br/><br/>The color that goes with each button is shown below.<br/><br/>"+
    "<div style=\"display: flex;\"><div class= 'inner_circle'><center><div style='padding-top: 20px'><img src='letterR.png'></div></center></div>"+
    "<div class= 'inner_circle'><center><div style='padding-top: 20px'><img src='letterY.png'></div></center></div>"+
    "<div class= 'inner_circle'><center><div style='padding-top: 20px'><img src='letterG.png'></div></center></div>"+
    "<div class= 'inner_circle'><center><div style='padding-top: 20px'><img src='letterB.png'></div></center></div></div>"+"<br/><p><br/>Use your two index fingers when responding.</p></div>";

    var memCorrectInARow = 0;
    var numColorTested = 0;
    var test_block = {
    	type: 'button-response',
	choices: ['<center><div class="inner_circle"><div style="padding-top: 20px;"><img src="letterR.png"></div></div></center>',
'<center><div class="inner_circle"><div style="padding-top: 20px;"><img src="letterY.png"></div></div></center>',
'<center><div class="inner_circle"><div style="padding-top: 20px;"><img src="letterG.png"></div></div></center>',
'<center><div class="inner_circle"><div style="padding-top: 20px;"><img src="letterB.png"></div></div></center>'],
	timing_post_trial: test_trials,
	timeline: test_trials,
	on_finish: function(data) {
		jsPsych.data.addDataToLastTrial({is_real_element: false});
		if(data.stimulus_type == "red" || data.stimulus_type == "blue" || data.stimulus_type == "green" || data.stimulus_type == "yellow"){
			var correct = false;
			if(data.stimulus_type == 'red' && data.button_pressed == 0){
				correct = true;
			} else if(data.stimulus_type == 'yellow' && data.button_pressed == 1){
				correct = true;
			} else if(data.stimulus_type == 'green' && data.button_pressed == 2) {
				correct = true;
			} else if(data.stimulus_type == 'blue' && data.button_pressed == 3) {
				correct = true;
			}
			if (correct) {
			    memCorrectInARow++;
			} else {
			    memCorrectInARow = 0;
			}
			jsPsych.data.addDataToLastTrial({correct: correct});
			if (memCorrectInARow > 9) 
			   jsPsych.endCurrentTimeline("You entered " + (memCorrectInARow+1 ) + " in a row correctly, lets continue.");
			numColorTested++;
			if (numColorTested > (maxtrial_nums * 4)-1)
				jsPsych.endTimeline("Giving up, not enought correct trials done");
		}
	}
    };

    // for a touch screen we do not have an enter button
    jQuery('body').on('touchstart', function() { jQuery('#inst').click(); jQuery('#instructions').click(); });
			
    var timeline = [];

    timeline.push( { type: 'button-response',
    button_html: '<button style="margin-left:350px" class="jspsych-btn jspsych-button-response-button">%choice%</button>',
    choices: ['<div class="inner_circle"><div style="padding-top: 10px"><h2>next</h2></div></div>'],
    is_html: true,
    stimulus: start_instructions } );

    timeline.push( { type: 'button-response',
    button_html: '<button style="margin-left:350px" class="jspsych-btn jspsych-button-response-button">%choice%</button>',
    choices: ['<div class="inner_circle"><div style="padding-top: 10px"><h2>next</h2></div></div>'],
    is_html: true, 
    stimulus: instructions } );

    timeline.push( test_block ); // add the test block (variable length, needs <N> correct answers)

    timeline.push( { type: 'button-response',
    button_html: '<button style="margin-left:350px" class="jspsych-btn jspsych-button-response-button">%choice%</button>',
    choices: ['<div class="inner_circle"><div style="padding-top: 10px"><h2>next</h2></div></div>'],
    is_html: true,
    stimulus: startreal } );

    // we want to run two experiments, the first will show all incongruent stimuli once and all neutral stimuli 3 times
    var uneqlist = [
	{ w: 1, stimulus: "<p class='RED'   >BLUE</p>", is_html: true, data: { stimulus_type: "inc", correct_color: 'RED' }, timing_response: 5000 },
	{ w: 1, stimulus: "<p class='YELLOW'   >RED</p>", is_html: true, data: { stimulus_type: "inc", correct_color: 'YELLOW' }, timing_response: 5000 },
	{ w: 1, stimulus: "<p class='GREEN'   >YELLOW</p>", is_html: true, data: { stimulus_type: "inc", correct_color: 'GREEN' }, timing_response: 5000 },
	{ w: 1, stimulus: "<p class='BLUE'   >GREEN</p>", is_html: true, data: { stimulus_type: "inc", correct_color: 'BLUE' }, timing_response: 5000 },
	{ w: 1, stimulus: "<p class='GREEN'   >BLUE</p>", is_html: true, data: { stimulus_type: "inc", correct_color: 'GREEN' }, timing_response: 5000 },
	{ w: 1, stimulus: "<p class='BLUE'   >RED</p>", is_html: true, data: { stimulus_type: "inc", correct_color: 'BLUE' }, timing_response: 5000 },
	{ w: 1, stimulus: "<p class='RED'   >YELLOW</p>", is_html: true, data: { stimulus_type: "inc", correct_color: 'RED' }, timing_response: 5000 },
	{ w: 1, stimulus: "<p class='YELLOW'   >GREEN</p>", is_html: true, data: { stimulus_type: "inc", correct_color: 'YELLOW' }, timing_response: 5000 },
	{ w: 1, stimulus: "<p class='YELLOW'   >BLUE</p>", is_html: true, data: { stimulus_type: "inc", correct_color: 'YELLOW' }, timing_response: 5000 },
	{ w: 1, stimulus: "<p class='GREEN'   >RED</p>", is_html: true, data: { stimulus_type: "inc", correct_color: 'GREEN' }, timing_response: 5000 },
	{ w: 1, stimulus: "<p class='BLUE'   >YELLOW</p>", is_html: true, data: { stimulus_type: "inc", correct_color: 'BLUE' }, timing_response: 5000 },
	{ w: 1, stimulus: "<p class='RED'   >GREEN</p>", is_html: true, data: { stimulus_type: "inc", correct_color: 'RED' }, timing_response: 5000 },

	{ w: 3, stimulus: "<p class='RED'   >MATH</p>", is_html: true, data: { stimulus_type: "neut", correct_color: 'RED' }, timing_response: 5000 },
	{ w: 3, stimulus: "<p class='YELLOW'   >DIVIDE</p>", is_html: true, data: { stimulus_type: "neut", correct_color: 'YELLOW' }, timing_response: 5000 },
	{ w: 3, stimulus: "<p class='GREEN'   >EQUAL</p>", is_html: true, data: { stimulus_type: "neut", correct_color: 'GREEN' }, timing_response: 5000 },
	{ w: 3, stimulus: "<p class='BLUE'   >ADD</p>", is_html: true, data: { stimulus_type: "neut", correct_color: 'BLUE' }, timing_response: 5000 },
	{ w: 3, stimulus: "<p class='GREEN'   >MATH</p>", is_html: true, data: { stimulus_type: "neut", correct_color: 'GREEN' }, timing_response: 5000 },
	{ w: 3, stimulus: "<p class='BLUE'   >DIVIDE</p>", is_html: true, data: { stimulus_type: "neut", correct_color: 'BLUE' }, timing_response: 5000 },
	{ w: 3, stimulus: "<p class='RED'   >EQUAL</p>", is_html: true, data: { stimulus_type: "neut", correct_color: 'RED' }, timing_response: 5000 },
	{ w: 3, stimulus: "<p class='YELLOW'   >ADD</p>", is_html: true, data: { stimulus_type: "neut", correct_color: 'YELLOW' }, timing_response: 5000 },
	{ w: 3, stimulus: "<p class='YELLOW'   >MATH</p>", is_html: true, data: { stimulus_type: "neut", correct_color: 'YELLOW' }, timing_response: 5000 },
	{ w: 3, stimulus: "<p class='GREEN'   >DIVIDE</p>", is_html: true, data: { stimulus_type: "neut", correct_color: 'GREEN' }, timing_response: 5000 },
	{ w: 3, stimulus: "<p class='BLUE'   >EQUAL</p>", is_html: true, data: { stimulus_type: "neut", correct_color: 'BLUE' }, timing_response: 5000 },
	{ w: 3, stimulus: "<p class='RED'   >ADD</p>", is_html: true, data: { stimulus_type: "neut", correct_color: 'RED' }, timing_response: 5000 }
    ];

    var all_uneq_trials = jsPsych.randomization.repeat( uneqlist, uneqlist.map(function(a) { return a.w; }) );

    // The second will show incongruent and neutral stimuli each twice
    var equallist = uneqlist.map( function(a) { a.w = 2; return a; });
    var all_equal_trials = jsPsych.randomization.repeat( equallist, equallist.map(function(a) { return a.w; }) );
    
    var block1 = {
    	type: 'button-response',
  choices: ['<center><div class="inner_circle"><div style="padding-top: 20px;"><img src="letterR.png"></div></div></center>',
'<center><div class="inner_circle"><div style="padding-top: 20px;"><img src="letterY.png"></div></div></center>',
'<center><div class="inner_circle"><div style="padding-top: 20px;"><img src="letterG.png"></div></div></center>',
'<center><div class="inner_circle"><div style="padding-top: 20px;"><img src="letterB.png"></div></div></center>'],
	timing_post_trial: post_trial_gap,
	timeline: all_uneq_trials,
	on_finish: function(data) {
  	        jsPsych.data.addDataToLastTrial({is_real_element: true, list: "inc: 1 - neut: 3"});
	    	var correct = false;
	   	if(data.correct_color == 'RED' && data.button_pressed == 0){
	      		correct = true;
	   	} else if(data.correct_color == 'YELLOW' && data.button_pressed == 1){
	      		correct = true;
	  	} else if(data.correct_color == 'GREEN' && data.button_pressed == 2) {
		        correct = true;
		} else if(data.correct_color == 'BLUE' && data.button_pressed == 3) {
		        correct = true;
		}
	   	jsPsych.data.addDataToLastTrial({correct: correct});
	}
    };

    timeline.push( block1 );

    var block2 = {
    	type: 'button-response',
  choices: ['<center><div class="inner_circle"><div style="padding-top: 20px;"><img src="letterR.png"></div></div></center>',
'<center><div class="inner_circle"><div style="padding-top: 20px;"><img src="letterY.png"></div></div></center>',
'<center><div class="inner_circle"><div style="padding-top: 20px;"><img src="letterG.png"></div></div></center>',
'<center><div class="inner_circle"><div style="padding-top: 20px;"><img src="letterB.png"></div></div></center>'],
	timing_post_trial: post_trial_gap,
	timeline: all_equal_trials,
	on_finish: function(data) {
	    jsPsych.data.addDataToLastTrial({is_real_element: true, list: "inc: 2 - neut: 2"});
	    	var correct = false;
	   	if(data.correct_color == 'RED' && data.button_pressed == 0){
	      		correct = true;
	   	} else if(data.correct_color == 'YELLOW' && data.button_pressed == 1){
	      		correct = true;
	  	} else if(data.correct_color == 'GREEN' && data.button_pressed == 2) {
		        correct = true;
		} else if(data.correct_color == 'BLUE' && data.button_pressed == 3) {
		        correct = true;
		}
	   	jsPsych.data.addDataToLastTrial({correct: correct});
	}
    };

    timeline.push( block2 );

    //separating text barrier slide at end of code
    timeline.push( { type: 'button-response',
        button_html: '<button style="margin-left:350px" class="jspsych-btn jspsych-button-response-button">%choice%</button>',
        choices: ['<div class="inner_circle"><div style="padding-top: 10px"><h2>next</h2></div></div>'],
        is_html: true, 
        stimulus: "<p>Great job, let's try another task!</p>" } );
    timeline.push( { type: 'button-response',
        button_html: '<button style="margin-left:350px" class="jspsych-btn jspsych-button-response-button">%choice%</button>',
        choices: ['<div class="inner_circle"><div style="padding-top: 10px"><h2>next</h2></div></div>'],
        is_html: true, 
    		stimulus: function() {
   			  return createStats( jsPsych.data.getData() );
		   }
    });
    //preload images for buttons
    var little_letters=["letterB.png","letterG.png","letterY.png","letterR.png"];
    //preload function on start calls the actual init function after loading. 
    jsPsych.pluginAPI.preloadImages(little_letters, function(){ startExperiment(); });
    
  function startExperiment(){
    jsPsych.init({
	timeline: timeline,
        on_finish: function(data) {

	    var d = {};
	    d.str_user = user_name;
			   
            ud = makeUnique( jsPsych.data.getData(), 'str_' );
	    
	    jQuery.post('code/php/events.php', { "data": JSON.stringify(ud), "date": moment().format(), "toplevel": JSON.stringify(d) }, function(data) {
			    if (typeof data.ok == 'undefined' || data.ok == 0) {
				alert('Error: ' + data.message);
			    }
			    // export as csv for download on client
			    exportToCsv("Stroop-Task_" + Site + "_" + SubjectID + "_" + Session + "_" + moment().format() + ".csv",
		  			jsPsych.data.getData());
			}, 'json').error(function() {
			    exportToCsv("Stroop-Task_" + Site + "_" + SubjectID + "_" + Session + "_" + moment().format() + ".csv",
		  			jsPsych.data.getData());			
			});
      document.body.innerHTML = '<center><form action="https://abcd-report.ucsd.edu/"><button style="height:200px;width:400px;font-size:48px;border-radius: 15px;" type="submit">Return to ABCD-Report Homepage</button></form></center>';
	}
    });

  }
    
function makeUnique( data, prefix ) {
    
    var build, key, destKey, value;
    
    build = {};
    if (typeof data === "object") {
	if (data instanceof Array) { // don't change the array, only traverse
	    for (var i = 0; i < data.length; i++) {
		data[i] = makeUnique(data[i], prefix);
	    }
	    return data;
	} else {
	    for (key in data) {
		// Get the destination key
		destKey = prefix + key;

		// Get the value
		value = data[key];

		value = makeUnique(value, prefix);

		build[destKey] = value;
	    }
	}	
    } else {
	return data;
    }

    return build;
}
</script>
</html>
    
