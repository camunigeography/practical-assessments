<?php

# Class to provide a practicals assessments system
require_once ('frontControllerApplication.php');
class practicalAssessments extends frontControllerApplication
{
	# Function to assign defaults additional to the general application defaults
	public function defaults ()
	{
		# Specify available arguments as defaults or as NULL (to represent a required argument)
		$defaults = array (
			'hostname' => 'localhost',
			'database' => 'practicalassessments',
			'username' => 'practicalassessments',
			'password' => NULL,
			'globalPeopleDatabase' => 'people',
			'table'			=> 'people',
			'applicationName'	=> 'Practicals assessments',
			'tabUlClass' => 'tabsflat',
			'authentication' => true,
			'administrators' => true,
			'yearStartMonth' => 12,
			'courseName' => NULL,
			'div' => 'practicalassessments',
			'useFeedback' => false,
			'useEditing' => true,
			'courseRegexp' => NULL,
			'tableSuffix' => NULL,
			'enableTheory' => false,
			'assessmentLabel' => 'assessment',
			'browseableAlwaysOpen' => false,
		);
		
		# Return the defaults
		return $defaults;
	}
	
	
	# Function assign additional actions
	public function actions ()
	{
		# Specify additional actions
		$actions = array (
			'theory' => array (
				'description' => false,
				'tab' => 'Theory',
				'url' => 'theory/',
				'icon' => 'page_white_horizontal',
				'enableIf' => $this->settings['enableTheory'],
			),
			'practical' => array (
				'description' => false,
				'tab' => 'Practical',
				'url' => 'practical/',
				'icon' => 'book_open',
			),
			'assessment' => array (
				'description' => false,
				'tab' => ucfirst ($this->settings['assessmentLabel']),
				'url' => 'assessment/',
				'icon' => 'pencil',
			),
			'status' => array (
				'tab' => 'Student progress',
				'description' => 'View progress of students',
				'administrator' => true,
			),
			'editing' => array (
				'description' => false,
				'url' => 'data/',
				'icon' => 'wand',
				'tab' => 'Setup',
				'administrator' => true,
			),
			'results' => array (
				'tab' => 'Results',
				'description' => 'View compiled results',
				'administrator' => true,
			),
			'resultsraw' => array (
				'description' => 'Results as CSV (raw data)',
				'url' => 'resultsraw.csv',
				'export' => true,
				'administrator' => true,
			),
			'resultscompiled' => array (
				'description' => 'Results as CSV (compiled)',
				'url' => 'resultscompiled.csv',
				'export' => true,
				'administrator' => true,
			),
			'upload' => array (
				'description' => 'Upload ' . ($this->settings['enableTheory'] ? 'theory/practical' : 'practical') . ' document',
				'usetab' => 'editing',
				'icon' => 'add',
				'administrator' => true,
			),
		);
		
		# Return the actions
		return $actions;
	}
	
	
	# Database structure definition
	public function databaseStructure ()
	{
		return "
			-- Administrators
			CREATE TABLE `administrators` (
			  `crsid` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `active` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
			  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
			  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
			  PRIMARY KEY (`crsid`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8mb4_unicode_ci COMMENT='Administrators';
			
			-- Questions
			CREATE TABLE `assessments` (
			  `id` int NOT NULL COMMENT 'Automatic key',
			  `topic__JOIN__stats1a__topics__reserved` tinyint NOT NULL COMMENT 'Topic (session)',
			  `questionNumber` tinyint NOT NULL COMMENT 'Question number',
			  `questionHtml` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Text of question',
			  `type` enum('','radiobuttons','checkboxes') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Answer structure',
			  `choices` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Choices',
			  `correctAnswerNumber` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Correct answer (1=first)',
			  `why` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Why text',
			  `help` text COLLATE utf8mb4_unicode_ci COMMENT 'Help text',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8mb4_unicode_ci COMMENT='Table of questions';
			
			-- Responses by students (for a specific year)
			CREATE TABLE `responses_2019_2020` (
			  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
			  `username` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Username',
			  `questionId__JOIN__stats1a__assessments__reserved` int NOT NULL COMMENT 'Question ID',
			  `isCorrect` int NOT NULL COMMENT 'Whether the student answered correctly',
			  `answerGiven` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The answer the student gave',
			  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8mb4_unicode_ci COMMENT='Table of responses by the student';
			
			-- User state (for a specific year)
			CREATE TABLE `state_2019_2020` (
			  `username` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Username',
			  `currentTopic` int NOT NULL DEFAULT '1' COMMENT 'Current topic',
			  `theoryFurthestPages` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1:1' COMMENT 'Theory: Pages - reached',
			  `theoryCurrentPages` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1:1' COMMENT 'Theory: Page of session - current',
			  `practicalFurthestPages` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1:1' COMMENT 'Practical: Pages - reached',
			  `practicalCurrentPages` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1:1' COMMENT 'Practical: Page of session - current',
			  `assessmentFurthestPages` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1:1' COMMENT 'Assessment: Pages - reached',
			  `assessmentCurrentPages` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1:1' COMMENT 'Assessment: Page of session - current',
			  `lastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Automatic timestamp',
			  PRIMARY KEY (`username`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8mb4_unicode_ci COMMENT='Table to store user state';
			
			-- Topics (i.e. sessions)
			CREATE TABLE `topics` (
			  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Automatic key',
			  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Title of topic (i.e. teaching session)',
			  `directionality` enum('Forward and back','Forward only') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Forward and back' COMMENT 'Direction that students can progress through this topic',
			  `opening` datetime DEFAULT NULL COMMENT 'Date/time this session opens',
			  `closing` datetime DEFAULT NULL COMMENT 'Date/time this session closes',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8mb4_unicode_ci COMMENT='Table of topics (i.e. each teaching session)';
		";
	}
	
	
	# Constructor
	protected function main ()
	{
		# Define the actions and their labels
		$this->actionLabels = array (
			'theory'		=> 'theory',
			'practical'		=> 'practical',
			'assessment'	=> $this->settings['assessmentLabel'],
		);
		if (!$this->settings['enableTheory']) {unset ($this->actionLabels['theory']);}
		
		# Determine the academic year
		require_once ('timedate.php');
		$this->academicYear = timedate::academicYear (10, $asRangeString = true);
		
		# Get the available topics
		$this->topics = $this->getTopics ();
		
		# Read/create the user's state
		$this->userState = $this->getUserState ();
		
	}
	
	
	# Function to read/create the user's state
	private function getUserState ()
	{
		# Get the details from the state table, creating the user if they are not yet present and reading back the newly-created user's details
		if (!$state = $this->databaseConnection->selectOne ($this->settings['database'], "state{$this->settings['tableSuffix']}", array ('username' => $this->user))) {
			$this->databaseConnection->insert ($this->settings['database'], "state{$this->settings['tableSuffix']}", array ('username' => $this->user));
			$state = $this->databaseConnection->selectOne ($this->settings['database'], "state{$this->settings['tableSuffix']}", array ('username' => $this->user));
		}
		
		# Rearrange the current pages strings into arrays
		$dimensions = array ('Furthest', 'Current');
		foreach ($this->actionLabels as $action => $label) {
			foreach ($dimensions as $dimension) {
				$state["{$action}{$dimension}Pages"]  = $this->unpackPagesString ($state["{$action}{$dimension}Pages"]);
			}
		}
		
		# Return the state
		return $state;
	}
	
	
	# Function to unpack the current pages string into an array, i.e. convert "1:30,2:7" into array(1=>30,2=>7), meaning session 1 is on page 30 and session 2 is on page 7
	private function unpackPagesString ($string, $sessionPrefix = '', $registeredSessionsOnlyFilledOut = false)
	{
		# Start an array of pages
		$pages = array ();
		
		# Split the string into key-value pairs
		$pairs = explode (',', $string);
		foreach ($pairs as $pair) {
			list ($session, $page) = explode (':', $pair);
			$pages[$sessionPrefix . $session] = $page;
		}
		
		# Add in other topics
		foreach ($this->topics as $session => $attributes) {
			if (!isSet ($pages[$sessionPrefix . $session])) {
				$pages[$sessionPrefix . $session] = 1;
			}
		}
		
		/*
		# Accept only registered topics if required, and fill these out
		if ($registeredSessionsOnlyFilledOut) {
			$cached = $pages;
			$pages = array ();
			foreach ($this->topics as $session => $title) {
				$pages[$sessionPrefix . $session] = (isSet ($cached[$sessionPrefix . $session]) ? 'p. ' . $cached[$sessionPrefix . $session] : '');
			}
		}
		*/
		
		# Sort
		ksort ($pages);
		
		# Return the array
		return $pages;
	}
	
	
	# Function to pack the current pages string
	private function packPagesString ($fieldname, $session, $page)
	{
		# Insert/update the current session's page into the array of current pages
		$this->userState[$fieldname][$session] = $page;
		
		# Re-implode the array
		$currentPages = $this->userState[$fieldname];
		ksort ($currentPages);
		$pairs = array ();
		foreach ($this->userState[$fieldname] as $session => $page) {
			$pairs[] = "{$session}:{$page}";
		}
		$string = implode (',', $pairs);
		
		# Return the assembled page string
		return $string;
	}
	
	
	# Function to get topics
	private function getTopics ($requireAssessments = true)
	{
		# Get the topics which have entries in the assessments table
		$query = "SELECT
			topics.*,
				UNIX_TIMESTAMP(opening) as opening,
				UNIX_TIMESTAMP(closing) as closing,
				IF(directionality = 'Forward only',1,0) AS forwardOnly
			FROM
		" . ($requireAssessments ? "
			assessments
			LEFT JOIN topics ON topic__JOIN__{$this->settings['database']}__topics__reserved = topics.id
		" : 'topics') . '
			ORDER BY topics.id
			;';
		$data = $this->databaseConnection->getData ($query, "{$this->settings['database']}.assessments");
		
		# Return the data
		return $data;
	}
	
	
	# Function to get questions for a topic
	private function getQuestions ($topic)
	{
		# Get the topics which have entries in the assessments table
		$query = "SELECT
				*
			FROM assessments
			WHERE topic__JOIN__{$this->settings['database']}__topics__reserved = {$topic}
			ORDER BY questionNumber
			;";
		$data = $this->databaseConnection->getData ($query, "{$this->settings['database']}.assessments");
		
		# Rearrange by question number
		$questions = array ();
		foreach ($data as $index => $question) {
			$id = $question['questionNumber'];
			$questions[$id] = $question;
		}
		
		# Cache a list of question IDs
		$questionIds = implode (',', array_keys ($data));
		
		# Turn the choices part into an array of options; tbhis is indexed from 1 rather than 0
		foreach ($questions as $index => $question) {
			$choices = explode ("\n", trim ($question['choices']));
			$questions[$index]['choices'] = array ();	// Overwrite current string
			foreach ($choices as $choiceIndex => $choice) {
				$number = $choiceIndex + 1;
				$questions[$index]['choices'][$number] = trim ($choice);
			}
		}
		
		# Merge in the user's responses if present, indexed by questionId
		#!# Could do as a JOIN earlier on, rather than this messy merging process
		$query = "SELECT
				*,
				questionId__JOIN__{$this->settings['database']}__assessments__reserved AS id
			FROM responses{$this->settings['tableSuffix']}
			WHERE
				    username = '{$this->user}'
				AND questionId__JOIN__{$this->settings['database']}__assessments__reserved IN({$questionIds})
			;";
		$responses = $this->databaseConnection->getData ($query, "{$this->settings['database']}.responses{$this->settings['tableSuffix']}");
		foreach ($questions as $index => $question) {
			$questionId = $question['id'];
			$questions[$index]['responded'] = (isSet ($responses[$questionId]) ? 1 : '');
		}
		
		# Return the data
		return $questions;
	}
	
	
	# Function to provide a theory session for a topic
	public function theory ($session)
	{
		return $this->pageNavigator ($session, __FUNCTION__);
	}
	
	
	# Function to provide a practical to work through a topic
	public function practical ($session)
	{
		return $this->pageNavigator ($session, __FUNCTION__);
	}
	
	
	# Function to provide a page navigation framework
	private function pageNavigator ($session, $type)
	{
		# Ensure the facility is open, unless they should be constantly open
		#!# Multiple checks - here and below
		if (!$this->settings['browseableAlwaysOpen']) {
			if (!$facilityIsOpen = $this->facilityIsOpen ($html, false, ($this->userIsAdministrator ? '<p class="warning">As an admin, however, you still have access.</p>' : ''))) {
				echo $html;
				if (!$this->userIsAdministrator) {return false;}
			}
		}
		
		# Select the session if the user has clicked on the tab
		if ($session == 'select') {
			return $this->redirectTo ($type);
		}
		
		# Validate the topic
		if (!isSet ($this->topics[$session])) {
			$this->page404 ();
			return false;
		}
		
		# Update the current topic
		$this->updateCurrentTopic ($session);
		
		# Start the HTML
		$html  = '';
		
		# Ensure the topic is available to the user
		if (!$this->settings['browseableAlwaysOpen']) {
			$sessionIsAvailable = $this->sessionIsAvailable ($session, $message);
			if (!$sessionIsAvailable) {
				$html  = "\n<p>This topic is {$message}.</p>";
				echo $html;
				return $html;
			}
			if (is_string ($sessionIsAvailable)) {
				$html .= "\n<p class=\"warning\">You can only see this topic as you are an admin; it is not available to ordinary users at this point in time.</p>";
			}
		}
		
		# Determine the available images for this topic
		$images = $this->getImages ($session, $type);
		$totalPages = count ($images);
		
		# Add in page state management (and end if a refresh has been called)
		if (!$pageStateControls = $this->pageStateManagement ($type, $session, $totalPages, "/topic{$session}/{$type}/", $this->topics[$session]['forwardOnly'])) {return;}
		
		# Determine the current page (i.e. image), padded as a value from 00 to 99
		$loadPageImage = str_pad ($this->userState["{$type}CurrentPages"][$session], 2, '0', STR_PAD_LEFT);
		
		# Create the page
		$html .= "\n\n<h2>Session {$session}: " . htmlspecialchars ($this->topics[$session]['name']) . '</h2>';
		$html .= $pageStateControls;
		//$html .= "\n\n<p class=\"clipwrapper\"><img src=\"{$this->baseUrl}/images/{$session}/{$loadPageImage}.png\" alt=\"Page\" class=\"clip\" /></p>";
		$html .= "\n\n<p><img src=\"{$this->baseUrl}/images/{$session}/{$type}/{$loadPageImage}.png\" alt=\"Page\" style=\"max-width: 100%;\" /></p>";
		
		# Add the help text if relevant
		$helpFileLocation = "{$this->baseUrl}/images/{$session}/{$type}/help/{$loadPageImage}.png";
		if (is_readable ($_SERVER['DOCUMENT_ROOT'] . $helpFileLocation)) {
			$html .= "\n<h3>Help page:</h3>";
			$html .= "\n\n<p class=\"clipwrapper\"><img src=\"{$this->baseUrl}/images/{$session}/{$type}/help/{$loadPageImage}.png\" alt=\"Help page\" class=\"clip\" /></p>";
		}
		
		# Show the HTML
		echo $html;
	}
	
	
	# Assessment
	public function assessment ($session)
	{
		# Ensure the facility is open
		if (!$facilityIsOpen = $this->facilityIsOpen ($html, false, ($this->userIsAdministrator ? '<p class="warning">As an admin, however, you still have access.</p>' : ''))) {
			echo $html;
			if (!$this->userIsAdministrator) {return false;}
		}
		
		# Select the session if the user has clicked on the tab
		if ($session == 'select') {
			return $this->redirectTo (__FUNCTION__);
		}
		
		# Validate the topic
		if (!isSet ($this->topics[$session])) {
			$this->page404 ();
			return false;
		}
		
		# Update the current topic
		$this->updateCurrentTopic ($session);
		
		# Start the HTML
		$html  = '';
		
		# Ensure the topic is available to the user
		$sessionIsAvailable = $this->sessionIsAvailable ($session, $message);
		if (!$sessionIsAvailable) {
			$html  = "\n<p>This topic is {$message}.</p>";
			echo $html;
			return $html;
		}
		
		# Determine the available images for this topic
		$questions = $this->getQuestions ($session);
		$totalPages = count ($questions);
		
		# Add in page state management (and end if a refresh has been called)
		if (!$pageStateControls = $this->pageStateManagement ('assessment', $session, $totalPages, "/topic{$session}/assessment/", true)) {return;}
		
		# Create the page title
		$title = "\n\n<h2>Session {$session}: " . htmlspecialchars ($this->topics[$session]['name']) . '</h2>';
		if (is_string ($sessionIsAvailable)) {
			$title = "\n<p class=\"warning\">You can only see this topic as you are an admin; it is not available to ordinary users at this point in time.</p>" . $title;
		}
		
		# Get the details for the selected questions
		$questionNumber = $this->userState['assessmentCurrentPages'][$session];
		if (!isSet ($questions[$questionNumber])) {
			$html .= $title;
			$html .= $pageStateControls;
			$html .= "<p>This question is not currently available, due to a setup error by the lecturer. Try another page.</p>";
			echo $html;
			return false;
		}
		$question = $questions[$questionNumber];
		
		# Add a warning about answering this the first time
		if (($questionNumber == 1) && (!$question['responded'])) {
			$html .= "\n" . '<p class="warning">Reminder: You will need to work through the associated practical in order to answer these questions.</p>';
			$html .= "\n" . '<p class="warning">Also, only the response from the <strong>first</strong> time that you answer each question is stored' . ($this->settings['assessmentLabel'] == 'assessment' ? ' for the assessment' : '') . '.</p>';
		}
		
		# If the question text is the older plaintext format, upgrade it to HTML
		if (!substr_count ($question['questionHtml'], '<p>')) {
			$question['questionHtml'] = application::formatTextBlock (application::makeClickableLinks ($question['questionHtml'], false, false, $target = false));
		}
		
		# Assemble the HTML
		$html .= "\n<div class=\"graybox clear\">";
		if ($question['responded']) {$html .= "\n<p><em>Note: As you have already answered this question, any submission you make again will not be saved.</em></p>";}
		$html .= "\n<h3>Question {$questionNumber}:</h3>";
		$html .= "\n<div id=\"question\">\n" . $question['questionHtml'] . "\n</div>";
		
		# Add the help text if relevant
		$diagramImage = str_pad ($questionNumber, 2, '0', STR_PAD_LEFT);
		$diagramFileLocation = "{$this->baseUrl}/images/{$session}/{$type}/diagrams/{$diagramImage}.png";
		if (is_readable ($_SERVER['DOCUMENT_ROOT'] . $diagramFileLocation)) {
			list ($width, $height, $type, $attributesString) = getimagesize ($_SERVER['DOCUMENT_ROOT'] . $diagramFileLocation);
			$html .= "\n\n<img src=\"{$diagramFileLocation}\" alt=\"Diagram\" {$attributesString} />";
		}
		
		# Add the hint if available
		if ($question['help']) {
			$html .= "\n<p>" . application::formatTextBlock ('Hint: ' . $question['help'], 'comment') . '</p>';
		}
		
		# Create the form
		require_once ('ultimateForm.php');
		$form = new form (array (
			'displayRestrictions' => false,
			'reappear' => 'disabled',
			'requiredFieldIndicator' => false,
			'formCompleteText' => false,
		));
		$widgetType = $question['type'];
		$outputTypes = array (
			'checkboxes'	=> 'special-setdatatype',
			'radiobuttons'	=> 'compiled',
			'input'			=> 'presented',
		);
		$form->$widgetType (array (
			'name'			=> "answer{$questionNumber}",
			'title'			=> 'Your answer',
			'required'		=> true,
			'values'		=> $question['choices'],	// Ignored by input type
			'size'			=> 60,						// Used only by input type
			'output' 		=> array ('processing' => $outputTypes[$widgetType]),
		));
		if ($result = $form->process ($html)) {
			
			# Is the answer correct?
			$answerIsCorrect = ($result["answer{$questionNumber}"] == $question['correctAnswerNumber']);
			
			# Show a result only for non-text answer types
			if ($widgetType != 'input') {
				
				# Show whether the user got it right
				$html .= "\n<h3>Result</h3>";
				if ($answerIsCorrect) {
					$html .= "\n" . '<p>' . $this->tick . ' Your answer was <strong>correct</strong>' . ($question['responded'] ? ' this time' : '') . '!</p>';
				} else {
					$html .= "\n" . '<p>' . $this->cross . ' Your answer was <strong>wrong</strong>' . ($question['responded'] ? ' this time' : '') . '.</p>';
				}
			}
			
			# Show the answer
			$html .= "\n<h3>Why?</h3>";
			$html .= "\n" . application::formatTextBlock ($question['why']) . '</p>';
			
			# if the user had not previously responded, insert the answer into the database
			if (!$question['responded']) {
				$insert = array (
					'username' => $this->user,
					"questionId__JOIN__{$this->settings['database']}__assessments__reserved" => $question['id'],
					'isCorrect' => ($answerIsCorrect ? '1' : '0'),
					'answerGiven' => $result["answer{$questionNumber}"],
				);
				if (!$this->databaseConnection->insert ($this->settings['database'], "responses{$this->settings['tableSuffix']}", $insert)) {
					#!# Error handling
					// application::dumpData ($this->databaseConnection->error ());
				}
				
				# Confirm save
				$html .= "\n<h3>Answer saved</h3>";
				$html .= "\n<p>Your answer has been saved.</p>";
			}
			
			# If the student is not repeating an already-answered question, give a link to the next question, updating the state if required
			if (!$question['responded']) {
				if ($questionNumber < $totalPages) {
					$nextPage = $questionNumber + 1;
					$this->updateCurrentPage ('assessment', $session, $nextPage, $doRefresh = false);
					$html .= "\n<h3>Next question</h3>";
					#!# Replace with a form to avoid repeat-submission of form answering a different question
					$html .= "\nYou can now move on to question {$nextPage}:</p>";
					$html .= '<form name="navigation" id="previousnext" action="' . $this->baseUrl . '/topic' . $session . '/assessment/" method="post">
						<input type="submit" name="navigation" value="Next &gt;" />
					</form>';
				} else {
					$html .= "\n<h3>End of assessment</h3>";
					$html .= "\nThis was the final question.</p>";
				}
			}
		}
		
		# Close the box
		$html .= "\n</div>";
		
		# Regenerate the state controls
		$pageStateControls = $this->pageStateManagement ('assessment', $session, $totalPages, "/topic{$session}/assessment/", true, $questionNumber);
		
		# Add the title and state controls to the start of the HTML
		$html = $title . $pageStateControls . $html;
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to redirect to the current session
	private function redirectTo ($type)
	{
		# Construct the target URL
		$currentTopic = $this->userState['currentTopic'];
		$url = $_SERVER['_SITE_URL'] . $this->baseUrl . "/topic{$currentTopic}/{$type}/";
		
		# Redirect or show message if the header-sending fails
		echo application::sendHeader (302, $url, true);
	}
	
	
	# Function to update the current session number
	private function updateCurrentTopic ($session)
	{
		# Update with the new session number if it has changed
		if ($this->userState['currentTopic'] != $session) {
			$this->databaseConnection->update ($this->settings['database'], "state{$this->settings['tableSuffix']}", array ('currentTopic' => $session), array ('username' => $this->user));
		}
	}
	
	
	# Function to manage page state
	private function pageStateManagement ($type, $session, $totalPages, $localUrl, $forwardOnly, $specifyCurrentPage = false)
	{
		# Create a list of available moves, as submittable form button text => page
		$availableMoves = array ();
		for ($i = 1; $i <= $totalPages; $i++) {
			if ($this->pageIsAvailable ($type, $session, $i)) {
				$availableMoves[$i] = $i;
			}
		}
		
		# Add links to previous/next page if available
		$currentPage = ($specifyCurrentPage ? $specifyCurrentPage : $this->userState["{$type}CurrentPages"][$session]);
		$theoreticalPreviousPage = $currentPage - 1;
		$theoreticalNextPage = $currentPage + 1;
		if (isSet ($availableMoves[$theoreticalPreviousPage])) {
			$availableMoves['< Previous'] = $theoreticalPreviousPage;
		}
		if (isSet ($availableMoves[$theoreticalNextPage])) {
			$availableMoves['Next >'] = $theoreticalNextPage;
		}
		
		# If a move is requested, find the requested move from the list of available moves
		$updateCurrentPage = false;
		if (isSet ($_POST['navigation'])) {
			foreach ($availableMoves as $submittableText => $page) {
				if ($_POST['navigation'] == $submittableText) {
					$updateCurrentPage = $page;
				}
			}
		}
		
		# Move to the page if a match is found
		if ($updateCurrentPage) {
			return $this->updateCurrentPage ($type, $session, $updateCurrentPage);
		}
		
		# Create a form to handle pagination
		$submitTo = $this->baseUrl . $localUrl;
		$formPagination = "\n<form name=\"navigation\" id=\"gotopage\" action=\"{$submitTo}\" method=\"post\">";
		$formPagination .= "Page: &nbsp; ";
		for ($i = 1; $i <= $totalPages; $i++) {
			
			# Switch between spans (page unavailable) and form elements (page available)
			if (in_array ($i, $availableMoves)) {
				$formPagination.= "\n\t<input type=\"submit\" name=\"navigation\" value=\"{$i}\"" . ($i == $currentPage ? ' class="selected"' : '') . ' />';
			} else {
				$formPagination .= "\n\t<span>{$i}</span>";
			}
		}
		$formPagination.= "\n</form>";
		
		# Create a form to handle previous/next
		$submitTo = $this->baseUrl . $localUrl;
		$formPreviousNext  = "\n<form name=\"navigation\" id=\"previousnext\" action=\"{$submitTo}\" method=\"post\">";
		$formPreviousNext .= "\n\t" . (isSet ($availableMoves['< Previous']) ? '<input type="submit" name="navigation" value="&lt; Previous" />' : '');
		#!# Avoid use of spacer
		$formPreviousNext .= "\n\t" . (isSet ($availableMoves['Next >']) ? '<input type="submit" name="navigation" value="Next &gt;" />' : '<img src="/images/furniture/spacer.gif" class="spacer" />');
		$formPreviousNext .= "\n</form>";
		
		# Compile the HTML
		$html  = "\n<p class=\"pagenumbers\">{$formPagination}</p>";
		$html .= $formPreviousNext;
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to update the page in the database
	private function updateCurrentPage ($type, $session, $page, $doRefresh = true)
	{
		# Start a list of state data updates
		$update = array ();
		
		# Save the requested page number as the new current page
		$update["{$type}CurrentPages"] = $this->packPagesString ("{$type}CurrentPages", $session, $page);
		
		# Save the requested page number as the new furthest page if it is higher than previously
		if ($this->userState["{$type}FurthestPages"][$session] < $page) {
			$update["{$type}FurthestPages"] = $this->packPagesString ("{$type}FurthestPages", $session, $page);
		}
		
		# Update the state data
		if (!$this->databaseConnection->update ($this->settings['database'], "state{$this->settings['tableSuffix']}", $update, array ('username' => $this->user))) {
			echo "\n<p>An error occured updating the page state.</p>";
			return false;
		}
		
		# Refresh the page, which will then load the new page; this also has the side-effect of avoiding POST warnings
		if ($doRefresh) {application::sendHeader ('refresh');}
		return false;
	}
	
	
	# Function to determine if a page is available
	private function pageIsAvailable ($type, $session, $page)
	{
		# The current page in this topic is by definition always available
		if ($page == $this->userState["{$type}CurrentPages"][$session]) {return true;}
		
		# If not forward-only, then earlier pages are available
		if (!$this->topics[$session]['forwardOnly']) {
			if ($page < $this->userState["{$type}FurthestPages"][$session]) {return true;}
		}
		
		# The user can go as far as the page after the furthest page they have reached
		if ($type == 'assessment') {
			if ($page <= $this->userState["{$type}FurthestPages"][$session]) {return true;}
		} else {
			if ($page <= ($this->userState["{$type}FurthestPages"][$session] + 1)) {return true;}
		}
		
		# Otherwise, the page is not available
		return false;
	}
	
	
	# Function to get the images for this topic
	private function getImages ($session, $type)
	{
		# Determine the directory containing the images
		$directory = $_SERVER['DOCUMENT_ROOT'] . $this->baseUrl . "/images/{$session}/{$type}/";
		
		# Get the PNG files in this directory
		require_once ('directories.php');
		if ($images = directories::listFiles ($directory, array ('PNG'), $directoryIsFromRoot = true)) {
			ksort ($images);
		}
		
		# Return the images
		return $images;
	}
	
	
	# Home page
	public function home ()
	{
		# Start the HTML
		$html  = "\n<p><strong>Welcome to the {$this->settings['courseName']} course.</strong></p>";
		$html .= "\n<p>Please select a session:</p>";
		$html .= $this->showSessions ();
		
		# Show the HTML
		echo $html;
	}
	
	
	# Student status page
	public function status ()
	{
		# Get the data from the state table
		$query = "SELECT
				state{$this->settings['tableSuffix']}.username as Username,
				{$this->settings['globalPeopleDatabase']}.forename AS Forename,
				{$this->settings['globalPeopleDatabase']}.surname AS Surname,
				colleges.college AS College,
				" . ($this->settings['enableTheory'] ? "state{$this->settings['tableSuffix']}.theoryCurrentPages," : '') . "
				state{$this->settings['tableSuffix']}.practicalCurrentPages,
				state{$this->settings['tableSuffix']}.assessmentCurrentPages
			FROM {$this->settings['database']}.state{$this->settings['tableSuffix']}
			LEFT OUTER JOIN {$this->settings['globalPeopleDatabase']}.people ON state{$this->settings['tableSuffix']}.username = {$this->settings['globalPeopleDatabase']}.people.username
			LEFT OUTER JOIN {$this->settings['globalPeopleDatabase']}.colleges ON {$this->settings['globalPeopleDatabase']}.college__JOIN__people__colleges__reserved = {$this->settings['globalPeopleDatabase']}.colleges.id
			ORDER BY surname,forename
		;";
		$data = $this->databaseConnection->getData ($query);
		
		# Unpack the current page strings
		#!# This needs to be reworked as it lists them with headings "Practical: Session 1 | Assessment: Session 1 | Practical: Session 2 | Assessment: Session 2 | etc." rather than combining them at cell-level
		#!# First four columns are empty if student data not loaded
		foreach ($data as $username => $user) {
			foreach ($this->actionLabels as $action => $label) {
				$currentPages = $this->unpackPagesString ($data[$username]["{$action}CurrentPages"], ucfirst ($action) . ': Session ', $registeredSessionsOnlyFilledOut = true);
				$data[$username] += $currentPages;
				unset ($data[$username]["{$action}CurrentPages"]);
			}
		}
		
		# Compile the HTML
		$html  = "\n" . '<!-- Enable table sortability: --><script language="javascript" type="text/javascript" src="/sitetech/sorttable.js"></script>';
		$html .= "<p>The table below shows all students who have logged in so far and the current pages they have reached, initially ordered by surname.</p>";
		$html .= application::htmlTable ($data, array (), $class = 'lines sortable" id="sortable', $keyAsFirstColumn = false);
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to list sessions
	private function showSessions ()
	{
		# Create a list
		$table = array ();
		foreach ($this->topics as $session) {
			$id = $session['id'];
			$name = htmlspecialchars ($session['name']);
			$sessionIsAvailable = $this->sessionIsAvailable ($id, $message);
			$table[$id] = array (
				'Session no.' => "Session {$id}:",
				'Topic name' => $name . ':',
			);
			foreach ($this->actionLabels as $action => $label) {
				$buttonAvailable = $sessionIsAvailable;
				if ($this->settings['browseableAlwaysOpen'] && $action != 'assessment') {
					$buttonAvailable = true;
				}
				$table[$id][$action] = ($buttonAvailable ? "<a href=\"{$this->baseUrl}/topic{$id}/{$action}/\" class=\"actions\"><img src=\"/images/icons/" . $this->actions[$action]['icon'] . '.png" class="icon" /> ' . ucfirst ($label) . '</a>' : ' &nbsp; <span class="faded">(' . ucfirst ($message) . ')</span>');
			}
		}
		
		# Compile the HTML
		$html  = application::htmlTable ($table, $this->actionLabels, 'topics lines', $keyAsFirstColumn = false, $uppercaseHeadings = true, $allowHtml = true);
		
		# Return the HTML
		return $html;
	}
	
	
	# Function to determine if a topic is available to the user; returns true (available) or a string (not available, stating why)
	private function sessionIsAvailable ($id, &$message)
	{
		# Get the details for this session
		$session = $this->topics[$id];
		
		# If before the opening time or after the closing time, deny
		if ($session['opening'] && (time () < $session['opening'])) {
			if ($this->userIsAdministrator) {
				return 'admin';
			} else {
				$message = 'not yet available - opens ' . date ('g.ia, j/M', $session['opening']);
				return false;
			}
		}
		if ($session['closing'] && (time () > $session['closing'])) {
			if ($this->userIsAdministrator) {
				return 'admin';
			} else {
				$message = 'no longer available';
				return false;
			}
		}
		
		# Otherwise passes tests OK (e.g. date is OK or no date limitation)
		return true;
	}
	
	
	# Results page
	public function results ()
	{
		# Start the HTML
		$html  = '';
		
		# Give a link to the CSV file
		$html .= "\n<h3>Raw data</h3>";
		$html .= "\n<p><a href=\"{$this->baseUrl}/resultsraw.csv\">Download CSV file of the raw data.</a></p>";
		
		$html .= "\n<h3>Compiled scores</h3>";
		$html .= "\n<p>The compiled data is below. <strong>Ensure you perform some manual verification checks against the raw data.</strong></p>";
		$html .= "\n<p><a href=\"{$this->baseUrl}/resultscompiled.csv\">Download CSV file of the table below.</a></p>";
		
		# Show a table of the compiled results
		list ($table, $headings) = $this->getResultDataCompiled ();
		$html .= application::htmlTable ($table, $headings, 'lines border alternate', $keyAsFirstColumn = false, $uppercaseHeadings = true);
		
		# Show the HTML
		echo $html;
	}
	
	
	# Function to create a CSV of the results - raw
	public function resultsraw ()
	{
		# Get the data
		$data = $this->getResultData ();
		
		# Create the CSV
		$this->resultsCsv ($data, 'rawdata');
	}
	
	
	# Function to create a CSV of the results - compiled
	public function resultscompiled ()
	{
		# Get the data
		list ($data, $headings) = $this->getResultDataCompiled ();
		
		# Create the CSV
		$this->resultsCsv ($data, 'compiled');
	}
	
	
	# Function to create a CSV of the results
	private function resultsCsv ($data, $type)
	{
		# Define the filename base
		$filenameBase = __CLASS__ . '_' . $this->settings['database'] . '_' . $this->academicYear . '_results' . '_' . $type;
		
		# Serve as CSV
		require_once ('csv.php');
		csv::serve ($data, $filenameBase);
	}
	
	
	# Function to compile the result data
	private function getResultDataCompiled ()
	{
		# Get the raw result data
		$data = $this->getResultData ();
		
		# Restructure the data so that it can be accessed as ->user->topic->questionNumber->isCorrect below
		$answers = array ();
		foreach ($data as $index => $result) {
			$username =	$result['username'];
			$topic = $result['topic'];
			$questionNumber = $result['questionNumber'];
			$answers[$username][$topic][$questionNumber] = $result['isCorrect'];
		}
		
		# Get all the available users
		$getUsersOnCourse = $this->getUsersOnCourse ();
		
		# Get the questions for each topic and a set of formatted headings
		$questions = array ();
		$truncateTo = 160;
		$headings = array ();
		foreach ($this->topics as $topic => $topicAttributes) {
			$questionData = $this->getQuestions ($topic);
			foreach ($questionData as $index => $question) {
				$questionNumber = $question['questionNumber'];
				#!# Not actually used
				$questionText = substr (trim (html_entity_decode (strip_tags ($question['questionHtml']))), 0, $truncateTo) . (strlen ($question['questionHtml']) > $truncateTo ? ' &hellip;' : '');
				$questions[$topic][$questionNumber] = $questionText;
				$headingString = "{$topic}.{$questionNumber}";
				$headings[$headingString] = "<abbr title=\"{$questionText}\">{$headingString}</abbr>";
			}
		}
		
		# Loop through each available user, to build up their results
		$table = array ();
		foreach ($getUsersOnCourse as $username => $user) {
			$scoreTotal = 0;
			$questionsTotal = 0;
			
			# Start with their username, forename and surname
			$table[$username] = $user;
			
			# Loop through each topic
			foreach ($questions as $topic => $topicQuestions) {
				$scoreThisTopic = 0;
				foreach ($topicQuestions as $questionNumber => $questionText) {
					$headingString = "{$topic}.{$questionNumber}";
					
					# Has the user answered the question?
					$hasAnswered = (isSet ($answers[$username]) && isSet ($answers[$username][$topic]) && isSet ($answers[$username][$topic][$questionNumber]));
					
					# Add the data, with an empty string if not answered
					$table[$username][$headingString] = ($hasAnswered ? $answers[$username][$topic][$questionNumber] : '');
					
					# Increment the score (add 1 or 0)
					$scoreThisTopic += ($hasAnswered ? $answers[$username][$topic][$questionNumber] : 0);
				}
				
				# Increment the count of questions
				$questionsThisTopic = count ($topicQuestions);
				$questionsTotal += $questionsThisTopic;
				
				# Add a summary
				$topicSummaryHeading = "Topic {$topic}: total / " . $questionsThisTopic;
				$table[$username][$topicSummaryHeading] = $scoreThisTopic;
				
				# Add to the total score
				$scoreTotal += $scoreThisTopic;
			}
			
			# Add grand totals
			$grandScoreHeading = "Grand total / " . $questionsTotal;
			$table[$username][$grandScoreHeading] = $scoreTotal;
			
			# Calculate a percentage score
			$percentage = ($scoreTotal / $questionsTotal) * 100;
			$table[$username]['Percentage'] = round ($percentage, 1);
		}
		
		# Return the table
		return array ($table, $headings);
	}
	
	
	# Function to get all users on the course
	private function getUsersOnCourse ()
	{
		# Get the data
		$query = "SELECT username, forename, surname
			FROM {$this->settings['globalPeopleDatabase']}.people
			WHERE course__JOIN__people__courses__reserved REGEXP '{$this->settings['courseRegexp']}'
			ORDER BY surname, forename, username";
		$data = $this->databaseConnection->getData ($query, "{$this->settings['globalPeopleDatabase']}.people");
		
		# Return the data
		return $data;
	}
	
	
	# Helper function to get the raw result data
	private function getResultData ()
	{
		# Define the query
		$query = "SELECT
			{$this->settings['database']}.responses{$this->settings['tableSuffix']}.id, {$this->settings['database']}.responses{$this->settings['tableSuffix']}.username,
			{$this->settings['globalPeopleDatabase']}.people.forename, {$this->settings['globalPeopleDatabase']}.people.surname,
			{$this->settings['database']}.assessments.topic__JOIN__{$this->settings['database']}__topics__reserved as topic, {$this->settings['database']}.assessments.questionNumber,
			{$this->settings['database']}.responses{$this->settings['tableSuffix']}.isCorrect
			
		FROM `responses{$this->settings['tableSuffix']}`
		LEFT JOIN {$this->settings['database']}.assessments ON questionId__JOIN__{$this->settings['database']}__assessments__reserved = {$this->settings['database']}.assessments.id
		LEFT JOIN {$this->settings['globalPeopleDatabase']}.people ON {$this->settings['database']}.responses{$this->settings['tableSuffix']}.username = {$this->settings['globalPeopleDatabase']}.people.username
		ORDER BY surname, forename, `topic`, questionNumber;";
		
		# Get the data
		$data = $this->databaseConnection->getData ($query);
		
		# Return the data
		return $data;
	}
	
	
	# Admin editing section, substantially delegated to the sinenomine editing component
	public function editing ($attributes = array (), $deny = false /* or supply an array */, $sinenomineExtraSettings = array ())
	{
		# Define supported tables
		$tables = array (
			'topics'		=> 'Topics - titles and opening/closing times',
			'assessments'	=> 'Questions for each topic',
		);
		
		# End if invalid table
		if (!isSet ($_GET['table'])) {
			$list = array ();
			foreach ($tables as $table => $label) {
				$list[] = "<a href=\"{$this->baseUrl}/data/{$table}/\"><strong>{$label}</strong></a>";
			}
			$list[] = "<a href=\"{$this->baseUrl}/upload.html\"><strong>Upload " . ($this->settings['enableTheory'] ? 'theory/practical' : 'practical') . " documents (PDF)</strong></a>";
			$html  = "<p>As an administrator, you can add/edit topics and questions:</p>";
			$html .= application::htmlUl ($list, 0, 'boxylist');
			echo $html;
			return false;
		}
		
		# Define sinenomine dataBinding attributes
		$attributes = array (
			array ($this->settings['database'], 'assessments', 'questionHtml', array ('editorToolbarSet' => 'BasicImage', 'editorFileBrowserStartupPath' => $this->baseUrl . '/images/', 'imageAlignmentByClass' => false, )),
		);
		
		
		# Run the standard front controller editing integration
		#!# Ideally need to supply explicit deny list
		echo parent::editing ($attributes, $deny);
	}
	
	
	# Upload page
	public function upload ()
	{
		# Start the HTML
		$html = '';
		
		# Get all topics loaded (not just those with assessments loaded yet)
		$this->topics = $this->getTopics ($requireAssessments = false);
		
		# Assemble the topics as an associative array
		$topics = array ();
		foreach ($this->topics as $id => $topic) {
			$topics[$id] = $id . ' - ' . $topic['name'];
		}
		
		# Define the storage directory for the PDFs and generated images
		$directory = $_SERVER['DOCUMENT_ROOT'] . $this->baseUrl . '/images/';
		
		# Create a form to upload a specific topic
		$form = new form (array (
			'formCompleteText'	=> false,
			'submitButtonText'		=> 'Copy over file(s)',
			'displayRestrictions' => false,
			'name' => false,
			'nullText' => false,
		));
		$form->heading ('p', 'Use this short form to upload a new ' . ($this->settings['enableTheory'] ? 'theory/practical' : 'practical') . '.');
		$form->heading ('p', 'Note that this will <strong>replace</strong> existing pages.');
		$form->heading ('p', 'The upload process takes about 30-60 seconds per file. Please do not refresh the page until you see the confirmation message.');
		$form->select (array (
		    'name'			=> 'topic',
		    'title'			=> 'Topic',
		    'values'		=> $topics,
		    'required'		=> true,
		));
		if ($this->settings['enableTheory']) {
			$form->radiobuttons (array (
			    'name'			=> 'type',
			    'title'			=> 'Type',
			    'values'		=> array ('theory', 'practical'),
			    'required'		=> true,
			));
		}
		$form->upload (array (
			'name'					=> 'file',
			'title'					=> 'PDF file',
			'directory'				=> $directory,
			'output'				=> array ('processing' => 'compiled'),
			'required'				=> true,
			'enableVersionControl'	=> true,
			'allowedExtensions'		=> array ('pdf'),
		));
		if ($result = $form->process ($html)) {
			
			# Create the top-level topic image directory
			$topicNumber = $result['topic'];
			$topicImagesDirectory = $directory . $topicNumber . '/';
			mkdir ($topicImagesDirectory);
			
			# Determine the type
			$type = ($this->settings['enableTheory'] ? $result['type'] : 'practical');
			
			# Rename the file
			#!# Annoyingly, can't yet use forcedFileName=%topic, because that doesn't currently support array type outputs, and output=processing=>rawcomponents is a later phase of processing
			$currentFilename = key ($result['file']);
			$newFilename = $topicImagesDirectory . $type . $topicNumber . '.pdf';
			rename ($currentFilename, $newFilename);	// Will overwrite any existing file of this name
			
			# Remove any current images
			$topicTypeImagesDirectory = $topicImagesDirectory . $type . '/';
			if (is_dir ($topicTypeImagesDirectory)) {
				require_once ('directories.php');
				$oldImageFiles = directories::listFiles ($topicTypeImagesDirectory, array (), $directoryIsFromRoot = true, $skipUnreadableFiles = false);
				foreach ($oldImageFiles as $oldImageFile => $attributes) {
					unlink ($topicTypeImagesDirectory . $oldImageFile);
				}
				rmdir ($topicTypeImagesDirectory);
			}
			
			# Assemble the conversion command
			# See:
			#  KEY LINK: https://stackoverflow.com/a/6605085/180733
			#  https://robfelty.com/2008/03/11/convert-pdf-to-png-with-imagemagick
			#  https://www.imagemagick.org/Usage/thumbnails/#pad
			#  https://unix.stackexchange.com/questions/20026/convert-images-to-pdf-how-to-make-pdf-pages-same-size
			#  https://stackoverflow.com/questions/6605006/convert-pdf-to-image-with-high-resolution
			#  https://www.imagemagick.org/script/command-line-options.php
			mkdir ($topicTypeImagesDirectory);
			$scale = '4';
			$width = 595 * $scale;		// 595 is A4 at 72dpi
			$height = 842 * $scale;		// 842 is A4 at 72dpi
			$cropLeft = 36 * $scale;	// Half an inch
			$cropRight = $cropLeft;
			$cropTop = 54 * $scale;		// 3/4 of an inch
			$cropBottom = 72 * $scale;	// 1 inch
			$density = 72 * $scale;		// Treat the original PDF as this DPI (which has the effect of resizing also)
			$command = "convert -verbose -density {$density} -crop " . round ($width - $cropLeft - $cropRight) . 'x' . round ($height - $cropTop - $cropBottom) . "+" . round ($cropLeft) . "+" . round ($cropTop) . " '{$newFilename}' -quality 100 -sharpen 0x1.0 -scene 1 '{$topicTypeImagesDirectory}%02d.png'";
			//echo $command;
			
			# Do the conversion
			exec ($command, $output, $returnVar);
			$success = ($returnVar == 0);
			
			# Confirm the result
			if ($success) {
				$html .= "\n<p>{$this->tick} The PDF was succesfully converted. <a href=\"{$this->baseUrl}/topic{$topicNumber}/{$type}/\"><strong>View the {$type} pages.</strong></a></p>";
			} else {
				$html .= "\n<p>An error occured during the conversion process. The server reported as follows:</p>";
				$html .= "\n<pre>" . htmlspecialchars ($command) . '</pre>';
				$html .= "\n<pre>" . nl2br (htmlspecialchars (implode ("\n", $output))) . '</pre>';
			}
		}
		
		# Show the HTML
		echo $html;
	}
}

?>
