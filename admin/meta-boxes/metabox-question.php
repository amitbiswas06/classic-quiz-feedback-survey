<?php
/**
 * Custom metaboxes for CPT cqfs_question
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\ADMIN\METABOXES\QUESTION;

class Question {

    const QstNonce = 'cqfs_question_nonce';
    protected $values;

    public function __construct() {
        //sanitize the global POST var. XSS ok.
		//all form inputs and security inputs
        $this->values = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        //CPT - cqfs_question
		add_action('add_meta_boxes', [ $this, 'cqfs_question_metaboxes' ]);
		//save
		add_action('save_post', [ $this, 'cqfs_question__group_save']);

    }

    /**
	 * CPT - cqfs_question
     * Add Metabox
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
    
    /**
     * Main custom metabox fields HTML
     */
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

    /**
     * Save custom meta fields for the metabox
     */
	public function cqfs_question__group_save($post_id){
        
        // check nonce
		if (!isset($this->values['_cqfs_qst_nonce']) || !wp_verify_nonce($this->values['_cqfs_qst_nonce'], self::QstNonce )){
			return $post_id;
		}

		// Check the user's permissions.
        if ( 'cqfs_question' == $this->values['post_type'] ) {
            if ( ! current_user_can( 'edit_cqfs_question', $post_id ) ) {
                return $post_id;
            }
        }
        
        // check autosave
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


}

$question = new Question;