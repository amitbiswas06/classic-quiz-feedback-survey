(function(){
/**
 * JavaScript for the admin screens
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

    function create_err_div(){

        let err_div = document.createElement('div');
        err_div.classList.add('selection-error-label');
        err_div.append( cqfs_admin_obj.err_msg );

        return err_div;
    }

    //run only for cqfs_question

    if( cqfs_admin_obj.post_type === 'cqfs_question'){
        
        const form = document.querySelector('form[name=post]');
        const correctAnsField = document.querySelector('#cqfs-correct-answers');
        const ansType = document.querySelector('#cqfs-answer-type');
        const answers = document.querySelector('#cqfs-answers');
        const answersArr = answers.value.split('\n');
        
        // console.log(errDiv)

        form.addEventListener('submit', e => {
            // e.preventDefault();

            // console.log(correctAnsField.value.split(','));

            const errDiv = document.querySelector('.selection-error-label');
            const check = text_value_numbers( correctAnsField.value, ansType.value, answersArr.length );

            if(errDiv){
                correctAnsField.parentElement.removeChild(errDiv);
                correctAnsField.parentElement.classList.remove('cqfs-selection-error');
            }
            
            if( !correctAnsField.value || !check ){
                e.preventDefault();
                correctAnsField.parentElement.classList.add('cqfs-selection-error');
                correctAnsField.parentElement.appendChild(create_err_div());
            }

        })

    }


    //run only for cqfs_entry

    if( cqfs_admin_obj.post_type === 'cqfs_entry'){
        // alert('I am here!');

        const all_acf_metabox = Array.from( document.querySelectorAll('#title, .acf-input input[type="text"], .acf-input input[type="radio"], .acf-input input[type="email"], .acf-input textarea'));
        const enableBtn = document.querySelector('#cqfs-entry-enable');//enable btn
        const disableBtn = document.querySelector('#cqfs-entry-disable');//disable btn
        const emailAdmin = document.querySelector('#cqfs-entry-email-admin');//email to admin btn
        const emailUser = document.querySelector('#cqfs-entry-email-user');//email to user btn
        const publishBtn = document.querySelector('input#publish');//WP publish/update button

        /**
         * run on edit page
         */
        if(cqfs_admin_obj.action === 'edit'){
            //disable all acf fields on load
            all_acf_metabox.map( v => v.disabled = true );
            [publishBtn, disableBtn].map( v => v.disabled = true );

            //enable edit mode on click
            enableBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.target.disabled = true;
                [publishBtn, disableBtn].map( v => v.disabled = false );
                all_acf_metabox.map( v => v.disabled ? v.disabled = false : '' );
            } );

            //disable edit mode on click
            disableBtn.addEventListener('click', (e) => {
                e.preventDefault();
                [publishBtn, e.target].map( v => v.disabled = true );
                enableBtn.disabled = false;
                all_acf_metabox.map( v => ! v.disabled ? v.disabled = true : '' );
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