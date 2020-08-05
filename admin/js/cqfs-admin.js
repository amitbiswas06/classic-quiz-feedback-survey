(function(){
/**
 * JavaScript for the CQFS admin screens
 */
    "use strict";

    /**
     * global scope in cqfs
     */
    function text_value_numbers( value, checkType = '', ansLength = 1 ){
        
        //only allow digits
        //must not start with 0
        //only comma separated values without spaces
        let exp = '';

        if( checkType === 'radio' ){
            exp = /^[1-9][0-9]*$/g;
        }else if( checkType === 'checkbox' ){
            exp = /^[1-9,][0-9,]*$/g;
        }else{
            exp = /^[1-9,][0-9,]*$/g;
        }

        let check = true;
        let valueArr = value.split(",");
        if( !value.match(exp) || valueArr.some( v => v > ansLength ) ){
            check = false;
        }

        return check;
    }

    /**
     * Numbers only from 1-100
     */
    function numberRange(valueToCheck){

        //only allow digits
        //must not start with 0
        //entry between 1-100
        const exp = /^[1-9][0-9]*$/g;

        let check = true;
        if( !valueToCheck.match(exp) || valueToCheck > 100 ){
            check = false;
        }

        return check;

    }

    function create_err_div(errMsg = ''){

        let err_div = document.createElement('div');
        err_div.classList.add('selection-error-label');
        err_div.append( errMsg );

        return err_div;
    }


    /**
     * run only for cqfs_question
     * Limited to addnew and edit page
     * base `post` from $screen in meta-boxes.php
     */

    if( cqfs_admin_obj.post_type === 'cqfs_question' && cqfs_admin_obj.base === 'post' ){
        
        const form = document.querySelector('form[name=post]');
        const cqfsRequired = Array.from(document.querySelectorAll('.cqfs-required input, .cqfs-required select, .cqfs-required textarea'));//required fields
        const correctAnsField = document.querySelector('#cqfs-correct-answers');
        const ansType = document.querySelector('#cqfs-answer-type');
        const answers = document.querySelector('#cqfs-answers');
        const answersArr = answers.value.split('\n');
        
        // console.log(errDiv)

        form.addEventListener('submit', e => {
            // e.preventDefault();

            // console.log(correctAnsField.value.split(','));

            const errDiv = document.querySelectorAll('.selection-error-label');
            const errClassDiv = document.querySelectorAll('.cqfs-selection-error');
            const check = text_value_numbers( correctAnsField.value, ansType.value, answersArr.length );

            if(errDiv.length){
                for( let i = 0; i < errDiv.length; i++ ){
                    errDiv[i].remove();
                }
            }

            if(errClassDiv.length){
                for( let i = 0; i < errClassDiv.length; i++ ){
                    errClassDiv[i].classList.remove('cqfs-selection-error');
                }
            }

            //validate the required fields that are not conditionally hidden
            cqfsRequired.map( el => {
                if( !el.value ){
                    e.preventDefault();
                    el.parentElement.classList.add('cqfs-selection-error');
                    el.parentElement.appendChild(create_err_div(cqfs_admin_obj.require_msg));
                }
            });
            
            //validate correct ans field
            if( !check ){
                e.preventDefault();
                correctAnsField.parentElement.classList.add('cqfs-selection-error');
                correctAnsField.parentElement.appendChild(create_err_div(cqfs_admin_obj.err_msg));
            }

        });

    }


    /**
     * run only for cqfs_build
     * Limited to addnew and edit page
     * base `post` from $screen in meta-boxes.php
     */
    if( cqfs_admin_obj.post_type === 'cqfs_build' && cqfs_admin_obj.base === 'post' ){

        // alert('Hello')
        const form = document.querySelector('form[name=post]');//main form
        const buildType = document.querySelector('#cqfs-build-type');//build type
        const cqfsRequired = Array.from(document.querySelectorAll('.cqfs-required input, .cqfs-required select, .cqfs-required textarea'));//required fields
        const percentage = document.querySelector('#cqfs-build-pass-percentage');
        const hiddenConditional = Array.from(document.querySelectorAll('.hidden-by-conditional-logic'));
        const shortcode = document.querySelector('#cqfs-build-shortcode');

        //check buildType and show/hide inputs
        if( buildType.value == "quiz" ){
            // alert('quiz')
            hiddenConditional.map( el => el.classList.remove('hidden-by-conditional-logic') );
        }else{
            hiddenConditional.map( el => el.classList.add('hidden-by-conditional-logic') );
        }

        buildType.addEventListener('change', (e) => {
            //check buildType and show/hide inputs
            if( e.target.value == "quiz" ){
                // alert('quiz')
                hiddenConditional.map( el => el.classList.remove('hidden-by-conditional-logic') );
            }else{
                hiddenConditional.map( el => el.classList.add('hidden-by-conditional-logic') );
            }
        });

        shortcode.addEventListener('click', (e) => e.target.select());

        form.addEventListener('submit', e => {
            // e.preventDefault();
            // alert('Hey')

            const errDiv = document.querySelectorAll('.selection-error-label');
            const errClassDiv = document.querySelectorAll('.cqfs-selection-error');
            const check = numberRange(percentage.value);

            //if error div found, remove it
            if(errDiv.length){
                for( let i = 0; i < errDiv.length; i++ ){
                    errDiv[i].remove();
                }
            }

            //if error class div found, remove the class
            if(errClassDiv.length){
                for( let i = 0; i < errClassDiv.length; i++ ){
                    errClassDiv[i].classList.remove('cqfs-selection-error');
                }
            }

            //validate percentage field. required but conditionally hidden.
            if( buildType.value == "quiz" ){
                if( !percentage.value || !check ){
                    e.preventDefault();
                    percentage.parentElement.classList.add('cqfs-selection-error');
                    percentage.parentElement.appendChild(create_err_div(cqfs_admin_obj.err_msg));
                }
            }

            //validate the required fields that are not conditionally hidden
            cqfsRequired.map( el => {
                if( !el.value ){
                    e.preventDefault();
                    el.parentElement.classList.add('cqfs-selection-error');
                    el.parentElement.appendChild(create_err_div(cqfs_admin_obj.require_msg));
                }
            });


        });

    }


    /**
     * run only for cqfs_entry
     */

    if( cqfs_admin_obj.post_type === 'cqfs_entry'){
        // alert('I am here!');

        const all_cqfs_metabox = Array.from( document.querySelectorAll('#title, .cqfs-input input[type="number"], .cqfs-input input[type="text"], .cqfs-input input[type="radio"], .cqfs-input input[type="email"], .cqfs-input textarea'));
        const enableBtn = document.querySelector('#cqfs-entry-enable');//enable btn
        const disableBtn = document.querySelector('#cqfs-entry-disable');//disable btn
        const emailAdmin = document.querySelector('#cqfs-entry-email-admin');//email to admin btn
        const emailUser = document.querySelector('#cqfs-entry-email-user');//email to user btn
        const publishBtn = document.querySelector('input#publish');//WP publish/update button

        /**
         * run on edit page
         */
        if(cqfs_admin_obj.action === 'edit'){
            //disable all cqfs fields on load
            all_cqfs_metabox.map( v => v.disabled = true );
            [publishBtn, disableBtn].map( v => v.disabled = true );

            //enable edit mode on click
            enableBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.target.disabled = true;
                [publishBtn, disableBtn].map( v => v.disabled = false );
                all_cqfs_metabox.map( v => v.disabled ? v.disabled = false : '' );
            } );

            //disable edit mode on click
            disableBtn.addEventListener('click', (e) => {
                e.preventDefault();
                [publishBtn, e.target].map( v => v.disabled = true );
                enableBtn.disabled = false;
                all_cqfs_metabox.map( v => ! v.disabled ? v.disabled = true : '' );
            } );

            //disable 'email to user' button if the form type is not quiz
            if( cqfs_admin_obj.entry_type !== 'quiz' ){
                emailUser.disabled = true;
                emailUser.setAttribute("style", "display: none;");
            }

            [emailAdmin, emailUser].map( v => v.addEventListener('click', (e) => e.preventDefault() ));

        }else if(cqfs_admin_obj.action === 'add'){
            /**
             * run on add new page
             */
            [enableBtn, disableBtn, emailAdmin, emailUser].map( v => {
                v.disabled = true;
                v.addEventListener('click', (e) => e.preventDefault() );
            } );

        }

        
    }// endif `cqfs_entry`
    

})();