<?php
/**
 * Custom metaboxes for CPT cqfs_build
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\ADMIN\METABOXES\BUILD;

class Build {

    const BuildNonce = 'cqfs_build_nonce';
    protected $values;

    public function __construct() {

        //sanitize the global POST var. XSS ok.
		//all form inputs and security inputs
        $this->values = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        //CPT - cqfs_build
		add_action('add_meta_boxes', [ $this, 'cqfs_build_metaboxes' ]);
		//save
		add_action('save_post', [ $this, 'cqfs_build__group_save']);

		// delete meta field as it is not in use anymore
		// delete_post_meta_by_key('cqfs_select_questions');

    }

    /**
     * CPT - cqfs_build
     * Add metaboxes
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

            //readonly shortcode metabox
			add_meta_box(
				'cqfs_build_shortcode',
				esc_html__('Shortcode', 'cqfs'),
				[ $this, 'cqfs_build__shortcode_html' ],
				$screen,
				'side'
			);

		}
	}

    /**
     * Readonly shortcode metabox html
     */
	public function cqfs_build__shortcode_html($post){
		?>
		<div class="cqfs-field">
			<div class="cqfs-label">
				<label for="cqfs-build-shortcode"><?php echo esc_html__('Build Shortcode','cqfs'); ?></label>
				<p class="description"><?php echo esc_html__('Click to select the shortcode. Then copy it.','cqfs'); ?></p>
			</div>
			<div class="cqfs-input"><input 
			type="text" 
			id="cqfs-build-shortcode" 
			value="<?php echo "&#91;cqfs id=". esc_attr($post->ID) ."&#93;"; ?>"
			readonly></div>
		</div>
		<?php
	}

    /**
     * Main cqfs_build custom meta field HTML
     */
	public function cqfs_build__group_html($post){

		// categories
		$categories = get_the_category($post->ID);

		//meta fields

		//build type | select
		$build_type = get_post_meta($post->ID, 'cqfs_build_type', true);

		//build layout | select
		$layout = get_post_meta($post->ID, 'cqfs_layout_type', true);

		//build-question category | category select
		// $build_question_cat = get_post_meta($post->ID, 'cqfs_select_questions', true);

		//build question order | select
		$question_order = get_post_meta($post->ID, 'cqfs_question_order', true);

		//build question orderby | select
		$question_orderby = get_post_meta($post->ID, 'cqfs_question_orderby', true);

		//build pass percentage | Number
		$pass_percentage = get_post_meta($post->ID, 'cqfs_pass_percentage', true);

		//build pass message | textarea
		$pass_message = get_post_meta($post->ID, 'cqfs_pass_message', true);

		//build fail message | textarea
		$fail_message = get_post_meta($post->ID, 'cqfs_fail_message', true);

		?>
		<div class="cqfs-hidden"><?php wp_nonce_field( self::BuildNonce, '_cqfs_build_nonce'); ?></div>
		<div class="cqfs-fields">

			<div class="cqfs-field">
				<div class="cqfs-info <?php echo !$categories ? esc_attr('alert-warnning') : esc_attr('alert-ok'); ?>">
					<div class="cqfs-label">
						<label><?php echo esc_html__('Questions by Categories','cqfs'); ?></label>
						<p class="description"><?php echo esc_html__('You have selected the following categories for questions.','cqfs'); ?></p>
					</div>
					<p class="cqfs-info-text"><?php 
						$cat_list = [];
						if( $categories ){
							foreach( $categories as $cat ){
								$cat_list[] = ucwords($cat->name);
							}
						}else{
							$cat_list[] = esc_html__('No categories are selected. Please select a category.', 'cqfs');
						}

						echo esc_html( implode(', ', $cat_list));

					?></p>
				</div>
			</div>

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
					<label for="cqfs-build-qst-order"><?php echo esc_html__('Question Order','cqfs'); ?><span class="cqfs-required"><?php esc_html_e('&#42;','cqfs'); ?></span></label>
					<p class="description"><?php echo esc_html__('Select the order of the questions.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<select name="cqfs[build-qst-order]" id="cqfs-build-qst-order" required>
						<?php
						$options = array(
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

			<div class="cqfs-field half cqfs-required">
				<div class="cqfs-label">
					<label for="cqfs-build-qst-orderby"><?php echo esc_html__('Orderby','cqfs'); ?><span class="cqfs-required"><?php esc_html_e('&#42;','cqfs'); ?></span></label>
					<p class="description"><?php echo esc_html__('Select the orderby of the questions.','cqfs'); ?></p>
				</div>
				<div class="cqfs-input">
					<select name="cqfs[build-qst-orderby]" id="cqfs-build-qst-orderby" required>
						<?php
						$options = array(
							'date'	=> esc_html__('Date', 'cqfs'),
							'ID'	=> esc_html__('ID', 'cqfs'),
							'title'	=> esc_html__('Title', 'cqfs'),
							'rand'	=> esc_html__('Random', 'cqfs'),
							'none'	=> esc_html__('No Order', 'cqfs'),
						);

						foreach( $options as $key => $val ){
							printf(
								'<option value="%s" %s>%s</option>',
								sanitize_key($key),
								$key == $question_orderby ? 'selected' : '',
								$val
							);
						}
						?>
					</select>
				</div>
			</div>

			<div class="cqfs-field cqfs-required">
				<div class="cqfs-label">
					<label for="cqfs-build-pass-percentage"><?php echo esc_html__('Pass Percentage','cqfs'); ?><span class="cqfs-required"><?php esc_html_e('&#42;','cqfs'); ?></span></label>
					<p class="description"><?php echo esc_html__('Set a percentage for pass mark. It is needed for all types as it will help us to assess things better.','cqfs'); ?></p>
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


    /**
     * Main custom meta field group saving
     */
	public function cqfs_build__group_save($post_id){

        // check nonce
		if (!isset($this->values['_cqfs_build_nonce']) || !wp_verify_nonce($this->values['_cqfs_build_nonce'], self::BuildNonce )){
			return $post_id;
		}

		// Check the user's permissions.
        if ( 'cqfs_build' == $this->values['post_type'] ) {
            if ( ! current_user_can( 'edit_cqfs_build', $post_id ) ) {
                return $post_id;
            }
        }
        
        // check autosave
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

			//save/update build question order
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_question_order'),
				sanitize_text_field($this->values['cqfs']['build-qst-order'])
			);

			//save/update build question orderby
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_question_orderby'),
				sanitize_text_field($this->values['cqfs']['build-qst-orderby'])
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
				esc_textarea($this->values['cqfs']['build-pass-msg'])
			);

			//save/update build fail message
			update_post_meta(
				esc_attr($post_id),
				sanitize_key('cqfs_fail_message'),
				esc_textarea($this->values['cqfs']['build-fail-msg'])
			);

		}

	} 

}

$build = new Build;