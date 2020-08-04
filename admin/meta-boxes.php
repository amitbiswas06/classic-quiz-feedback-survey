<?php
/**
 * Custom metaboxes for few screens in admin
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\ADMIN\METABOXES;
use CQFS\INC\UTIL\Utilities as util;
use CQFS\ROOT\CQFS as CQFS;

class MetaBoxes {

	const QstNonce = 'cqfs_question_nonce';
	const BuildNonce = 'cqfs_build_nonce';
	const EntryNonce = 'cqfs_entry_nonce';

	protected $values;

    /**
     * constructor
     */
    public function __construct(){

		//sanitize the global POST var. XSS ok.
		//all form inputs and security inputs
		$this->values = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		//admin scripts
		add_action('admin_enqueue_scripts', [$this, 'cqfs_admin_scripts']);

		//CPT - cqfs_question
		add_action('add_meta_boxes', [ $this, 'cqfs_question_metaboxes' ]);
		//save
		add_action('save_post', [ $this, 'cqfs_question__group_save']);

        //CPT - cqfs_build
		add_action('add_meta_boxes', [ $this, 'cqfs_build_metaboxes' ]);
		//save
		add_action('save_post', [ $this, 'cqfs_build__group_save']);
        
        //CPT - cqfs_entry
		add_action('add_meta_boxes', [ $this, 'cqfs_entry_metaboxes' ]);

	}
	
	/**
	 * CPT - cqfs_question
	 */
	public function cqfs_question_metaboxes(){
		$screens = ['cqfs_question'];
		foreach ($screens as $screen) {

			//questions meta box together
			add_meta_box(
				'cqfs_question_group',
				esc_html__('Question Data', 'cqfs'),
				[ $this, 'cqfs_question__group_html' ],
				$screen,
				'normal',
				'high'
			);
		}

	}
	
	public function cqfs_question__group_html($post){

		//meta fields

		//given answers | textarea
		$answers = get_post_meta($post->ID, 'cqfs_answers', true);
		//answer type | select
		$answer_type = get_post_meta($post->ID, 'cqfs_answer_type', true);
		//correct answer | text
		$correct_answer = get_post_meta($post->ID, 'cqfs_correct_answer', true);
		//note | textarea
		$note = get_post_meta($post->ID, 'cqfs_additional_note', true);

		?>
		<div class="cqfs-hidden"><?php wp_nonce_field( self::QstNonce, '_cqfs_qst_nonce'); ?></div>
		<div class="cqfs-fields">

			<div class="cqfs-field cqfs-required">
				<div class="cqfs-label">
					<label for="cqfs-answers"><?php echo esc_html__('Answers','cqfs'); ?><span class="cqfs-required"><?php esc_html_e('&#42;','cqfs'); ?></span></label>
					<p class="description"><?php echo esc_html__('Please use separate line for each answer. Each line will be considered as 1, 2, 3 ... and so on.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<textarea name="cqfs[answers]" id="cqfs-answers" rows="6" required><?php 
					echo esc_html($answers); ?></textarea>
				</div>
			</div>

			<div class="cqfs-field half cqfs-required">
				<div class="cqfs-label">
					<label for="cqfs-answer-type"><?php echo esc_html__('Answer Type','cqfs'); ?><span class="cqfs-required"><?php esc_html_e('&#42;','cqfs'); ?></span></label>
					<p class="description"><?php echo esc_html__('If this question have more than one correct answer, select check boxes. Otherwise select radio button.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<select name="cqfs[answer-type]" id="cqfs-answer-type" required>
						<?php
						$options = array(
							''			=> esc_html__('Please Select...', 'cqfs'),
							'radio'		=> esc_html__('Radio Button', 'cqfs'),
							'checkbox'	=> esc_html__('Check Boxes', 'cqfs'),
						);

						foreach( $options as $key => $val ){
							printf(
								'<option value="%s" %s>%s</option>',
								sanitize_key($key),
								$key == $answer_type ? 'selected' : '',
								$val
							);
						}
						?>
					</select>
				</div>
			</div>

			<div class="cqfs-field half cqfs-required">
				<div class="cqfs-label">
					<label for="cqfs-correct-answers"><?php echo esc_html__('Correct Answer','cqfs'); ?><span class="cqfs-required"><?php esc_html_e('&#42;','cqfs'); ?></span></label>
					<p class="description"><?php echo esc_html__('Consider the answers (above) in each line as 1, 2, 3... and so on. Please separate with comma for multiple correct answers. eg; 2,3 (no space)','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<input type="text" name="cqfs[correct-answer]" id="cqfs-correct-answers" value="<?php echo esc_attr($correct_answer); ?>" required>
				</div>
			</div>
			
			<div class="cqfs-field">
				<div class="cqfs-label">
					<label for="cqfs-note"><?php echo esc_html__('Additional Note','cqfs'); ?></label>
					<p class="description"><?php echo esc_html__('This is hidden in questionnaire and showed in the result page for build type "quiz".','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<textarea name="cqfs[note]" id="cqfs-note" rows="6"><?php 
					echo esc_html($note); ?></textarea>
				</div>
			</div>

		</div>

		<?php
	}

	public function cqfs_question__group_save($post_id){
		
		if (!isset($this->values['_cqfs_qst_nonce']) || !wp_verify_nonce($_POST['_cqfs_qst_nonce'], self::QstNonce )){
			return $post_id;
		}

		// Check the user's permissions.
        if ( 'cqfs_question' == $this->values['post_type'] ) {
            if ( ! current_user_can( 'edit_cqfs_question', $post_id ) ) {
                return $post_id;
            }
        }
		
		if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE){
			return $post_id;
		}	
		
		//if all ok above, update post

		//save cqfs_answers
		if (array_key_exists('cqfs', $this->values )) {

			//save/update answers
            update_post_meta(
                esc_attr($post_id),
                sanitize_key('cqfs_answers'),
                esc_textarea($this->values['cqfs']['answers'])
            );

			//save/update answer-type
            update_post_meta(
                esc_attr($post_id),
                sanitize_key('cqfs_answer_type'),
                sanitize_text_field($this->values['cqfs']['answer-type'])
			);
			
			//save/update correct-answers
			update_post_meta(
                esc_attr($post_id),
                sanitize_key('cqfs_correct_answer'),
                sanitize_text_field( rtrim( $this->values['cqfs']['correct-answer'], ',') )
			);
			
			//save/update note
            update_post_meta(
                esc_attr($post_id),
                sanitize_key('cqfs_additional_note'),
                esc_textarea($this->values['cqfs']['note'])
			);
			
		}	


	}


    /**
     * CPT - cqfs_build
     */
    public function cqfs_build_metaboxes(){
		$screens = ['cqfs_build'];
		foreach ($screens as $screen) {

			//main metabox
			add_meta_box(
				'cqfs_build_group',
				esc_html__('Build Data', 'cqfs'),
				[ $this, 'cqfs_build__group_html' ],
				$screen,
				'normal',
				'high'
			);

			add_meta_box(
				'cqfs_build_shortcode',
				esc_html__('Shortcode', 'cqfs'),
				[ $this, 'cqfs_build__shortcode_html' ],
				$screen,
				'side'
			);

		}
	}

	public function cqfs_build__shortcode_html($post){
		?>
		<div class="cqfs-field">
			<div class="cqfs-label">
				<label for="cqfs-build-shortcode"><?php echo esc_html__('Build Shortcode','cqfs'); ?></label>
				<p class="description"><?php echo esc_html__('Click to copy the shortcode.','cqfs'); ?></p>
			</div>
			<div class="cqfs-input"><input 
			type="text" 
			id="cqfs-build-shortcode" 
			value="<?php echo "&#91;cqfs id=". esc_attr($post->ID) ."&#93;"; ?>"
			readonly></div>
		</div>
		<?php
	}

	public function cqfs_build__group_html($post){

		//meta fields

		//build type | select
		$build_type = get_post_meta($post->ID, 'cqfs_build_type', true);

		//build layout | select
		$layout = get_post_meta($post->ID, 'cqfs_layout_type', true);

		//build-question category | category select
		$build_question_cat = get_post_meta($post->ID, 'cqfs_select_questions', true);

		//build question order | select
		$question_order = get_post_meta($post->ID, 'cqfs_question_order', true);

		//build pass percentage | Number
		$pass_percentage = get_post_meta($post->ID, 'cqfs_pass_percentage', true);

		//build pass message | textarea
		$pass_message = get_post_meta($post->ID, 'cqfs_pass_message', true);

		//build fail message | textarea
		$fail_message = get_post_meta($post->ID, 'cqfs_fail_message', true);

		?>
		<div class="cqfs-hidden"><?php wp_nonce_field( self::BuildNonce, '_cqfs_build_nonce'); ?></div>
		<div class="cqfs-fields">

			<div class="cqfs-field half cqfs-required">
				<div class="cqfs-label">
					<label for="cqfs-build-type"><?php echo esc_html__('Build Type','cqfs'); ?><span class="cqfs-required"><?php esc_html_e('&#42;','cqfs'); ?></span></label>
					<p class="description"><?php echo esc_html__('Select a build type.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<select name="cqfs[build-type]" id="cqfs-build-type" required>
						<?php
						$options = array(
							''			=> esc_html__('Please Select...', 'cqfs'),
							'quiz'		=> esc_html__('Quiz', 'cqfs'),
							'feedback'	=> esc_html__('Feedback', 'cqfs'),
							'survey'	=> esc_html__('Survey', 'cqfs'),
						);

						foreach( $options as $key => $val ){
							printf(
								'<option value="%s" %s>%s</option>',
								sanitize_key($key),
								$key == $build_type ? 'selected' : '',
								$val
							);
						}
						?>
					</select>
				</div>
			</div>

			<div class="cqfs-field half cqfs-required">
				<div class="cqfs-label">
					<label for="cqfs-build-layout"><?php echo esc_html__('Layout Type','cqfs'); ?><span class="cqfs-required"><?php esc_html_e('&#42;','cqfs'); ?></span></label>
					<p class="description"><?php echo esc_html__('Select a layout type.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<select name="cqfs[build-layout]" id="cqfs-build-layout" required>
						<?php
						$options = array(
							''		=> esc_html__('Please Select...', 'cqfs'),
							'multi'	=> esc_html__('Multi Page Questions', 'cqfs'),
							'single'=> esc_html__('Single Page Questions', 'cqfs'),
						);

						foreach( $options as $key => $val ){
							printf(
								'<option value="%s" %s>%s</option>',
								sanitize_key($key),
								$key == $layout ? 'selected' : '',
								$val
							);
						}
						?>
					</select>
				</div>
			</div>

			<div class="cqfs-field half cqfs-required">
				<div class="cqfs-label">
					<label for="cqfs-build-question-category"><?php echo esc_html__('Select Questions by Category','cqfs'); ?><span class="cqfs-required"><?php esc_html_e('&#42;','cqfs'); ?></span></label>
					<p class="description"><?php echo esc_html__('Select a Category.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<select name="cqfs[build-question-category]" id="cqfs-build-question-category" required>
						<option value=""><?php echo esc_html__('Please Select...', 'cqfs'); ?></option>
						<?php
						$categories = get_categories();
						foreach( $categories as $cat ){
							printf(
								'<option value="%s" %s>%s</option>',
								sanitize_key($cat->term_id),
								$cat->term_id == $build_question_cat ? 'selected' : '',
								esc_html($cat->name)
							);
						}
						?>
					</select>
				</div>
			</div>

			<div class="cqfs-field half cqfs-required">
				<div class="cqfs-label">
					<label for="cqfs-build-qst-order"><?php echo esc_html__('Question Order','cqfs'); ?><span class="cqfs-required"><?php esc_html_e('&#42;','cqfs'); ?></span></label>
					<p class="description"><?php echo esc_html__('Select the order of the questions.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<select name="cqfs[build-qst-order]" id="cqfs-build-qst-order" required>
						<?php
						$options = array(
							''		=> esc_html__('Please Select...', 'cqfs'),
							'asc'	=> esc_html__('Ascending', 'cqfs'),
							'desc'	=> esc_html__('Descending', 'cqfs'),
						);

						foreach( $options as $key => $val ){
							printf(
								'<option value="%s" %s>%s</option>',
								sanitize_key($key),
								$key == $question_order ? 'selected' : '',
								$val
							);
						}
						?>
					</select>
				</div>
			</div>

			<div class="cqfs-field cqfs-conditional-required hidden-by-conditional-logic">
				<div class="cqfs-label">
					<label for="cqfs-build-pass-percentage"><?php echo esc_html__('Pass Percentage','cqfs'); ?><span class="cqfs-required"><?php esc_html_e('&#42;','cqfs'); ?></span></label>
					<p class="description"><?php echo esc_html__('Set a percentage for pass mark.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<div class="cqfs-input-append"><?php esc_html_e('&#37;','cqfs'); ?></div>
					<div class="cqfs-input-wrap">
						<input type="number" 
						name="cqfs[build-pass-percentage]" 
						class="cqfs-is-appended"
						value="<?php echo esc_attr($pass_percentage); ?>"
						id="cqfs-build-pass-percentage">
					</div>
				</div>
			</div>

			<div class="cqfs-field hidden-by-conditional-logic">
				<div class="cqfs-label">
					<label for="cqfs-build-pass-msg"><?php echo esc_html__('Pass Message (optional)','cqfs'); ?></label>
					<p class="description"><?php echo esc_html__('Leave empty for default.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<textarea name="cqfs[build-pass-msg]" id="cqfs-build-pass-msg" rows="4" maxlength="500"><?php 
					echo esc_html($pass_message); ?></textarea>
				</div>
			</div>

			<div class="cqfs-field hidden-by-conditional-logic">
				<div class="cqfs-label">
					<label for="cqfs-build-fail-msg"><?php echo esc_html__('Fail Message (optional)','cqfs'); ?></label>
					<p class="description"><?php echo esc_html__('Leave empty for default.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<textarea name="cqfs[build-fail-msg]" id="cqfs-build-fail-msg" rows="4" maxlength="500"><?php 
					echo esc_html($fail_message); ?></textarea>
				</div>
			</div>

		</div>

		<?php
	}


	public function cqfs_build__group_save($post_id){

		if (!isset($this->values['_cqfs_build_nonce']) || !wp_verify_nonce($_POST['_cqfs_build_nonce'], self::BuildNonce )){
			return $post_id;
		}

		// Check the user's permissions.
        if ( 'cqfs_build' == $this->values['post_type'] ) {
            if ( ! current_user_can( 'edit_cqfs_build', $post_id ) ) {
                return $post_id;
            }
        }
		
		if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE){
			return $post_id;
		}	
		
		//if all ok above, update post

		//save cqfs_build
		if (array_key_exists('cqfs', $this->values )) {

			//save/update build type
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_build_type'),
				sanitize_text_field($this->values['cqfs']['build-type'])
			);

			//save/update layout type
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_layout_type'),
				sanitize_text_field($this->values['cqfs']['build-layout'])
			);

			//save/update build question category
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_select_questions'),
				sanitize_text_field($this->values['cqfs']['build-question-category'])
			);

			//save/update build question order
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_question_order'),
				sanitize_text_field($this->values['cqfs']['build-qst-order'])
			);

			//save/update build pass percentage
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_pass_percentage'),
				sanitize_text_field($this->values['cqfs']['build-pass-percentage'])
			);

			//save/update build pass message
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_pass_message'),
				sanitize_text_field($this->values['cqfs']['build-pass-msg'])
			);

			//save/update build fail message
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_fail_message'),
				sanitize_text_field($this->values['cqfs']['build-fail-msg'])
			);

		}


	}
    
    /*********** end cqfs_build ***********/

    /**
     * CPT - cqfs_entry
     */
    public function cqfs_entry_metaboxes(){
		$screens = ['cqfs_entry'];
		foreach ($screens as $screen) {
			
			//edit buttons
			add_meta_box(
				'cqfs_entry_edit_btn',
				esc_html__('Edit This Entry', 'cqfs'),
				[ $this, 'cqfs_build__edit_btn_metabox' ],
                $screen,
                'side'
			);

			//email buttons
			add_meta_box(
				'cqfs_entry_email_btn',
				esc_html__('Email Options', 'cqfs'),
				[ $this, 'cqfs_build__email_btn_metabox' ],
                $screen,
                'side'
			);
		}
	}

	public function cqfs_build__edit_btn_metabox($post){

		printf(
			'<div class="cqfs-metabox">
				<button id="cqfs-entry-enable" class="button button-primary button-large">%s</button>
				<button id="cqfs-entry-disable" class="button button-secondary button-large">%s</button>
			</div>',
			esc_html__('Enable', 'cqfs'),
			esc_html__('Disable', 'cqfs')
		);
	}
	
	public function cqfs_build__email_btn_metabox($post){

		printf(
			'<div class="cqfs-metabox">
				<button id="cqfs-entry-email-admin" class="button button-secondary button-large">%s</button>
				<button id="cqfs-entry-email-user" class="button button-secondary button-large">%s</button>
			</div>',
			esc_html__('Email to admin', 'cqfs'),
			esc_html__('Email to user', 'cqfs')
		);
    }


	/**
	 * admin enqueue scripts
	 */
	public function cqfs_admin_scripts() {

		//grab the current post ID
		$post_id = filter_input(INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT);

		//grab "action=edit" screen
		$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
		
		// get current admin screen, or null
		$screen = get_current_screen(); 
		// var_dump($screen);

		// verify admin screen object
		if ( is_object( $screen ) ) {
			// enqueue only for specific post types
			if ( in_array( $screen->post_type, ['cqfs_entry','cqfs_question','cqfs_build'] ) ) {

				//set only for cqfs_entry edit screen
				if( $screen->post_type === 'cqfs_entry' && isset($post_id) && isset($action) && $action === 'edit' ){
					$cqfs_entry_form_type = util::cqfs_entry_obj($post_id)['form_type'];
					$edit_page = $action;
				}elseif( $screen->post_type === 'cqfs_entry' && $screen->action === 'add'){
					//set add new page
					$cqfs_entry_form_type = '';
					$edit_page = esc_html($screen->action);
				}else{
					//all other page
					$cqfs_entry_form_type = '';
					$edit_page = '';
				}
				
				// enqueue styles
				wp_enqueue_style(
					'cqfs-admin-style', 
					plugin_dir_url(__FILE__) . 'css/cqfs-admin-style.css', 
					array(), 
					CQFS::CQFS_VERSION,
					'all'
				);

				// enqueue script
				wp_enqueue_script(
					'cqfs_admin_script', 
					plugin_dir_url(__FILE__) . 'js/cqfs-admin.js', 
					NULL, 
					CQFS::CQFS_VERSION, 
					true
				);
				// localize script, create a custom js object
				wp_localize_script(
					'cqfs_admin_script',
					'cqfs_admin_obj',
					[
						'url' 			=> admin_url('admin-ajax.php'),
						'post_type'		=> esc_html( $screen->post_type ),
						'base'			=> esc_html( $screen->base ),
						'entry_type'	=> esc_html( $cqfs_entry_form_type ),//only for cqfs_entry
						'action'		=> esc_html( $edit_page ),//only for cqfs_entry
						'err_msg'		=> esc_html__('Required field contains invalid entry.','cqfs'),
						'require_msg'	=> esc_html__('Required field value cannot be null.','cqfs'),
					]
				);
			}
		}
		
	}



}

$meta_boxes = new MetaBoxes;