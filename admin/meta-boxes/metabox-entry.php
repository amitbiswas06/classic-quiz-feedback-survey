<?php
/**
 * Custom metaboxes for CPT cqfs_entry
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\ADMIN\METABOXES\ENTRY;

class Entry {

    const EntryNonce = 'cqfs_entry_nonce';
    protected $values;

    public function __construct() {

        //sanitize the global POST var. XSS ok.
		//all form inputs and security inputs
        $this->values = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        //CPT - cqfs_entry
		add_action('add_meta_boxes', [ $this, 'cqfs_entry_metaboxes' ]);
		//save
		add_action('save_post', [ $this, 'cqfs_entry__group_save']);
        
    }

    /**
     * CPT - cqfs_entry
     * Add metaboxes
     */
    public function cqfs_entry_metaboxes(){
		$screens = ['cqfs_entry'];
		foreach ($screens as $screen) {

			//main metabox
			add_meta_box(
				'cqfs_entry_group',
				esc_html__('Entry Data', 'cqfs'),
				[ $this, 'cqfs_entry__group_html' ],
                $screen,
				'normal',
				'high'
			);
			
			//edit buttons
			add_meta_box(
				'cqfs_entry_edit_btn',
				esc_html__('Edit This Entry', 'cqfs'),
				[ $this, 'cqfs_entry__edit_btn_metabox' ],
                $screen,
                'side'
			);

			//email buttons
			add_meta_box(
				'cqfs_entry_email_btn',
				esc_html__('Email Options', 'cqfs'),
				[ $this, 'cqfs_entry__email_btn_metabox' ],
                $screen,
                'side'
			);
		}
	}

    /**
     * Metabox HTML of cqfs_entry
     * for custom edit buttons
     */
	public function cqfs_entry__edit_btn_metabox($post){

		printf(
			'<div class="cqfs-metabox">
				<button id="cqfs-entry-enable" class="button button-primary button-large">%s</button>
				<button id="cqfs-entry-disable" class="button button-secondary button-large">%s</button>
			</div>',
			esc_html__('Enable', 'cqfs'),
			esc_html__('Disable', 'cqfs')
		);
	}
    
    /**
     * Metabox HTML of cqfs_entry
     * for custom email buttons
     */
	public function cqfs_entry__email_btn_metabox($post){

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
     * Metabox HTML of cqfs_entry
     * for main post fields group
     */
	public function cqfs_entry__group_html($post){

		// meta fields

		// entry form id | number
		$form_id = get_post_meta($post->ID, 'cqfs_entry_form_id', true);

		// entry form type | text
		$form_type = get_post_meta($post->ID, 'cqfs_entry_form_type', true);

		// entry result for quiz | radio
		$result = get_post_meta($post->ID, 'cqfs_entry_result', true);

		// percentage obtained in quiz | number
		$percent = get_post_meta($post->ID, 'cqfs_entry_percentage', true);

		// entry questions | textarea
		$questions = get_post_meta($post->ID, 'cqfs_entry_questions', true);

		// entry answers | textarea
		$answers = get_post_meta($post->ID, 'cqfs_entry_answers', true);

		// entry for answer status in each line | textarea
		$status = get_post_meta($post->ID, 'cqfs_entry_status', true);

		// entry notes for each question | textarea
		$notes = get_post_meta($post->ID, 'cqfs_entry_notes', true);

		// entry remarks for quiz | textarea
		$remarks = get_post_meta($post->ID, 'cqfs_entry_remarks', true);

		// entry user email
		$email = get_post_meta($post->ID, 'cqfs_entry_user_email', true);

		?>
		<div class="cqfs-hidden"><?php wp_nonce_field( self::EntryNonce, '_cqfs_entry_nonce'); ?></div>
		<div class="cqfs-fields">

			<div class="cqfs-field half">
				<div class="cqfs-label">
					<label for="cqfs-entry-form-id"><?php echo esc_html__('Form ID (cqfs build)','cqfs'); ?></label>
					<p class="description"><?php echo esc_html__('The form ID which user have submitted.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<input type="number" 
					name="cqfs[entry-form-id]" 
					min=1 
					value="<?php echo esc_attr($form_id); ?>"
					id="cqfs-entry-form-id">
				</div>
			</div>

			<div class="cqfs-field half">
				<div class="cqfs-label">
					<label for="cqfs-entry-form-type"><?php echo esc_html__('Form Type (cqfs build)','cqfs'); ?></label>
					<p class="description"><?php echo esc_html__('The form type which user have submitted.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<input type="text" 
					name="cqfs[entry-form-type]" 
					value="<?php echo esc_attr($form_type); ?>"
					id="cqfs-entry-form-type">
				</div>
			</div>

			<div class="cqfs-field half">
				<div class="cqfs-label">
					<label for="cqfs-entry-result"><?php echo esc_html__('Result','cqfs'); ?></label>
					<p class="description"><?php echo esc_html__('The Quiz result.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<ul id="cqfs-entry-result" class="cqfs-radio-list horizontal">
						<?php
						$list = array(
							'passed' => esc_html__('Passed', 'cqfs'),
							'failed' => esc_html__('Failed', 'cqfs'),
						);
						foreach( $list as $key => $val ){
							printf(
								'<li><label><input name="cqfs[entry-result]" type="radio" value="%s" %s>%s</label></li>',
								esc_attr($key),
								$key == $result ? esc_attr('checked') : '',
								esc_html($val)
							);
						}
						?>
					</ul>
				</div>
			</div>

			<div class="cqfs-field half">
				<div class="cqfs-label">
					<label for="cqfs-entry-percent"><?php echo esc_html__('Percentage','cqfs'); ?></label>
					<p class="description"><?php echo esc_html__('Percentage obtained in quiz.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<input type="number" 
					name="cqfs[entry-percent]" 
					min=0 
					value="<?php echo esc_attr($percent); ?>"
					id="cqfs-entry-percent">
				</div>
			</div>

			<div class="cqfs-field">
				<div class="cqfs-label">
					<label for="cqfs-entry-questions"><?php echo esc_html__('Questions','cqfs'); ?></label>
					<p class="description"><?php echo esc_html__('The CQFS question list for this entry. Each line break represents a question.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<textarea name="cqfs[entry-questions]" id="cqfs-entry-questions" rows="6"><?php 
					echo esc_html($questions); ?></textarea>
				</div>
			</div>

			<div class="cqfs-field">
				<div class="cqfs-label">
					<label for="cqfs-entry-answers"><?php echo esc_html__('Answers','cqfs'); ?></label>
					<p class="description"><?php echo esc_html__('The CQFS answer list for this entry. Each line break represents an answer.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<textarea name="cqfs[entry-answers]" id="cqfs-entry-answers" rows="6"><?php 
					echo esc_html($answers); ?></textarea>
				</div>
			</div>

			<div class="cqfs-field">
				<div class="cqfs-label">
					<label for="cqfs-entry-status"><?php echo esc_html__('Status','cqfs'); ?></label>
					<p class="description"><?php echo esc_html__('The CQFS answer status list for this entry. Each line break represents a status.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<textarea name="cqfs[entry-status]" id="cqfs-entry-status" rows="6"><?php 
					echo esc_html($status); ?></textarea>
				</div>
			</div>

			<div class="cqfs-field">
				<div class="cqfs-label">
					<label for="cqfs-entry-notes"><?php echo esc_html__('Notes','cqfs'); ?></label>
					<p class="description"><?php echo esc_html__('The additional notes for each question. Each line break represents a note.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<textarea name="cqfs[entry-notes]" id="cqfs-entry-notes" rows="6"><?php 
					echo esc_html($notes); ?></textarea>
				</div>
			</div>

			<div class="cqfs-field">
				<div class="cqfs-label">
					<label for="cqfs-entry-remarks"><?php echo esc_html__('Remarks','cqfs'); ?></label>
					<p class="description"><?php echo esc_html__('Pass or fail message.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<textarea name="cqfs[entry-remarks]" id="cqfs-entry-remarks" rows="6"><?php 
					echo esc_html($remarks); ?></textarea>
				</div>
			</div>

			<div class="cqfs-field half">
				<div class="cqfs-label">
					<label for="cqfs-entry-email"><?php echo esc_html__('User Email','cqfs'); ?></label>
					<p class="description"><?php echo esc_html__('Email ID of the user who have submitted.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<input type="email" 
					name="cqfs[entry-email]" 
					value="<?php echo sanitize_email($email); ?>"
					id="cqfs-entry-email">
				</div>
			</div>	

		</div>
		<?php
	}


    /**
     * Save Metabox for cqfs_entry
     * Main post meta fields group
     */
	public function cqfs_entry__group_save($post_id){

        // nonce verification
        if (!isset($this->values['_cqfs_entry_nonce']) || !wp_verify_nonce($this->values['_cqfs_entry_nonce'], self::EntryNonce )){
			return $post_id;
		}

		// Check the user's permissions.
        if ( 'cqfs_entry' == $this->values['post_type'] ) {
            if ( ! current_user_can( 'edit_cqfs_entry', $post_id ) ) {
                return $post_id;
            }
        }
        
        // checking if auto saving
		if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE){
			return $post_id;
		}	
		
		//if all ok above, update post

		//save cqfs_build
		if (array_key_exists('cqfs', $this->values )) {

			//save/update entry form id
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_entry_form_id'),
				sanitize_text_field($this->values['cqfs']['entry-form-id'])
			);

			//save/update entry form type
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_entry_form_type'),
				sanitize_text_field($this->values['cqfs']['entry-form-type'])
			);

			//save/update entry result, sp. case
			//check if field is not empty as this is a radio inp and not required field
			if( !empty($this->values['cqfs']['entry-result']) ){
				update_post_meta(
					esc_attr($post_id),
					sanitize_key('cqfs_entry_result'),
					sanitize_text_field($this->values['cqfs']['entry-result'])
				);
			}

			//save/update percentage obtained
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_entry_percentage'),
				sanitize_text_field($this->values['cqfs']['entry-percent'])
			);

			//save/update question list
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_entry_questions'),
				esc_textarea($this->values['cqfs']['entry-questions'])
			);

			//save/update answer list
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_entry_answers'),
				esc_textarea($this->values['cqfs']['entry-answers'])
			);

			//save/update answer status
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_entry_status'),
				esc_textarea($this->values['cqfs']['entry-status'])
			);

			//save/update notes
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_entry_notes'),
				esc_textarea($this->values['cqfs']['entry-notes'])
			);

			//save/update pass fail message as remarks
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_entry_remarks'),
				esc_textarea($this->values['cqfs']['entry-remarks'])
			);

			//save/update user email
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_entry_user_email'),
				sanitize_email($this->values['cqfs']['entry-email'])
			);

		}

	}

}

$entry = new Entry;