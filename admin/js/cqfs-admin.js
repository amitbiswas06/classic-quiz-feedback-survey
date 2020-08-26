(function(){
/**
 * JavaScript for the CQFS admin screens
 */
    "use strict";

    // global submit check
    let isSubmit = false;

    /**
     * Fadein function
     * 
     * @param {node} el 
     */
    function fadeIn(el){

        el.style.display = "block";
        el.style.opacity = 0;
        el.classList.remove('display-none');

        setTimeout( () => {
            el.style.opacity = 1
        }, 200 );

    }

    /**
     * Fadeout function
     * 
     * @param {node} el 
     */
    function fadeOut(el){

        el.style.opacity = 0;

        setTimeout( () => {
            el.style.display = "none";
        }, 200 );

    }

    /**
     * Ajax POST method implementation:
     * 
     * @param {post url} url 
     * @param {post data} data 
     */
    async function postData( url = '', data ) {
        // Default options are marked with *
        const response = await fetch(url, {
            method: 'POST', // *GET, POST, PUT, DELETE, etc.
            mode: 'cors', // no-cors, *cors, same-origin
            cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
            credentials: 'same-origin', // include, *same-origin, omit
            headers: {
                'Accept': 'application/json'
            },
            redirect: 'follow', // manual, *follow, error
            referrerPolicy: 'same-origin', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
            body: data
        });

        return response;

    }

    /**
     * Check text input value with the conditional logic
     * for cqfs_question only
     * 
     * @param {string} value        input value for answer input
     * @param {string} checkType    checkbox or radio question type
     * @param {number} ansLength    length of question
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
     * Check numbers only from 1-100
     * 
     * @param {string} valueToCheck text input value
     * @returns `bool`              returns boolean
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

    /**
     * Creates and return error div html
     * 
     * @param {string} errMsg string as message
     * @returns `node` HTML node with error message
     */
    function create_err_div(errMsg = ''){

        let err_div = document.createElement('div');
        err_div.classList.add('selection-error-label');
        err_div.append( errMsg );

        return err_div;
    }

    /**
     * Stores and returns form data as a encoded string
     * 
     * @param {HTMLFormElement} form   form HTML node
     * @returns `string`    form data as a encoded string
     */
    function storeFormData( form ){

        // observer form data change if user is leaving without saving
        let formData = new FormData(form);
        let fromArray = [];

        //push to the form array which is declared blank array above
        for(let pair of formData.entries()) {
            fromArray.push(pair[0] + '=' + pair[1]);
        }

        // map and join the form data array into a string
        let finalArr = fromArray.map( v => v ).join('&');
        // encode and store the form data string
        let final = encodeURIComponent( finalArr );

        return final;

    }


    /**
     * run only for cqfs_question
     * Limited to "add-new" and "edit" page
     * base `post` from $screen in meta-boxes.php
     */

    if( cqfs_admin_obj.post_type === 'cqfs_question' && cqfs_admin_obj.base === 'post' ){
        
        const form = document.querySelector('form[name=post]');
        const cqfsRequired = Array.from(document.querySelectorAll('.cqfs-required input, .cqfs-required select, .cqfs-required textarea'));//required fields
        const correctAnsField = document.querySelector('#cqfs-correct-answers');
        const ansType = document.querySelector('#cqfs-answer-type');
        const answers = document.querySelector('#cqfs-answers');
        
        // form submit and validations
        form.addEventListener('submit', e => {

            // set the global variable `isSubmit` to true
            isSubmit = true;

            const answersArr = answers.value.split('\n');
            const errDiv = document.querySelectorAll('.selection-error-label');
            const errClassDiv = document.querySelectorAll('.cqfs-selection-error');
            const check = text_value_numbers( correctAnsField.value, ansType.value, answersArr.length );

            // if error div found, remove it at this point
            if(errDiv.length){
                for( let i = 0; i < errDiv.length; i++ ){
                    errDiv[i].remove();
                }
            }

            // if error class found, remove at this point
            if(errClassDiv.length){
                for( let i = 0; i < errClassDiv.length; i++ ){
                    errClassDiv[i].classList.remove('cqfs-selection-error');
                }
            }

            // validate the required fields that are not conditionally hidden
            cqfsRequired.map( el => {
                if( !el.value ){
                    e.preventDefault();
                    el.parentElement.classList.add('cqfs-selection-error');
                    el.parentElement.appendChild(create_err_div(cqfs_admin_obj.require_msg));
                }
            });
            
            // validate correct ans field
            if( !check ){
                e.preventDefault();
                correctAnsField.parentElement.classList.add('cqfs-selection-error');
                correctAnsField.parentElement.appendChild(create_err_div(cqfs_admin_obj.err_msg));
            }

        });

        // event listner for window object `beforeunload`
        const initForm = storeFormData( form );
        window.addEventListener("beforeunload", function (e) {

            // new form data store before unload event
            const newForm = storeFormData( form );

            // bail early if form is submitting
            if( isSubmit ){
                return;
            }

            // finally check if form data changes are there.
            if( initForm != newForm ){
                e.preventDefault();
                return e.returnValue = '';
            }

        });
        

    }

    /**
     * run only for cqfs_build
     * Limited to "add-new" and "edit" page
     * base `post` from $screen in meta-boxes.php
     */
    if( cqfs_admin_obj.post_type === 'cqfs_build' && cqfs_admin_obj.base === 'post' ){

        const form = document.querySelector('form[name=post]');//main form
        const buildType = document.querySelector('#cqfs-build-type');//build type
        const cqfsRequired = Array.from(document.querySelectorAll('.cqfs-required input, .cqfs-required select, .cqfs-required textarea'));//required fields
        const percentage = document.querySelector('#cqfs-build-pass-percentage');
        const hiddenConditional = Array.from(document.querySelectorAll('.hidden-by-conditional-logic'));
        const shortcode = document.querySelector('#cqfs-build-shortcode');

        //check buildType and show/hide inputs
        if( buildType.value == "quiz" ){
            hiddenConditional.map( el => el.classList.remove('hidden-by-conditional-logic') );
        }else{
            hiddenConditional.map( el => el.classList.add('hidden-by-conditional-logic') );
        }

        buildType.addEventListener('change', (e) => {
            //check buildType and show/hide inputs
            if( e.target.value == "quiz" ){
                hiddenConditional.map( el => el.classList.remove('hidden-by-conditional-logic') );
            }else{
                hiddenConditional.map( el => el.classList.add('hidden-by-conditional-logic') );
            }
        });

        shortcode.addEventListener('click', (e) => e.target.select());

        form.addEventListener('submit', e => {

            // set the global variable `isSubmit` to true
            isSubmit = true;

            const errDiv = document.querySelectorAll('.selection-error-label');
            const errClassDiv = document.querySelectorAll('.cqfs-selection-error');
            const percentage_check = numberRange(percentage.value);

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

            //validate percentage field. required.
            if( !percentage.value || !percentage_check ){
                e.preventDefault();
                percentage.parentElement.classList.add('cqfs-selection-error');
                percentage.parentElement.appendChild(create_err_div(cqfs_admin_obj.err_msg));
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

        // event listner for window object `beforeunload`
        const initForm = storeFormData( form );
        window.addEventListener("beforeunload", function (e) {

            // new form data store before unload event
            const newForm = storeFormData( form );

            // bail early if form is submitting
            if( isSubmit ){
                return;
            }

            // finally check if form data changes are there.
            if( initForm != newForm ){
                e.preventDefault();
                return e.returnValue = '';
            }

        });

    }

    /**
     * run only for cqfs_entry
     * Limited to "add-new" and "edit" page
     * base `post` from $screen in meta-boxes.php
     */

    if( cqfs_admin_obj.post_type === 'cqfs_entry' && cqfs_admin_obj.base === 'post' ){

        const form = document.querySelector('form[name=post]');//main form
        const all_cqfs_metabox = Array.from( document.querySelectorAll('#title, .cqfs-input input[type="number"], .cqfs-input input[type="text"], .cqfs-input input[type="radio"], .cqfs-input input[type="email"], .cqfs-input textarea'));
        const enableBtn = document.querySelector('#cqfs-entry-enable');//enable btn
        const disableBtn = document.querySelector('#cqfs-entry-disable');//disable btn

        const email_metabox = document.querySelector('#cqfs_entry_email_btn');
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
                email_metabox.setAttribute("style", "display: none;");
            }

            /**
             * Email to user
             */
            // login modal
            const email_to_Modal = document.getElementById('cqfs-email-user');
            const closeBtn = document.querySelector('.cqfs-close');
            const alertMsg = document.querySelector('.cqfs-alert-message');
            const email_to_form = document.querySelector('form[name="cqfs-email-user-form"]');

            // run only if form is available
            if(email_to_Modal){

                const loader = email_to_Modal.querySelector('.cqfs-loader');
                const submitBtn = email_to_Modal.querySelector('button[type="submit"]');

                emailUser.addEventListener('click', e => {
                    e.preventDefault();
                    fadeIn(email_to_Modal);
                });

                // When the user clicks on <span> (x), close the modal
                closeBtn.onclick = function() {
                    fadeOut(email_to_Modal)
                }

                // When the user clicks anywhere outside of the modal, close it
                window.onclick = function(event) {
                    if ( event.target == email_to_Modal ) {
                        fadeOut(email_to_Modal)
                    }
                }

                email_to_form.addEventListener('submit', (e) => {

                    // prevent default
                    e.preventDefault();
                    loader.classList.remove('display-none');
                    submitBtn.disabled = true;
                    const formData = new FormData(e.target);
                    formData.append('ajax_request', 1);
        
                    postData( cqfs_admin_obj.ajax_url, formData )
                    .then(response => response.json() )
                    .then( obj => {

                        // return obj;
                        if( obj.success ){
                            e.target.remove();
                            alertMsg.classList.remove('display-none');
                            alertMsg.classList.add('success');
                            alertMsg.innerHTML = obj.data.message;

                            setTimeout( () => {
                                fadeOut(email_to_Modal);
                            }, 2000 );
                        }

                        if( !obj.success ){
                            loader.classList.add('display-none');
                            submitBtn.disabled = false;
                            alertMsg.classList.remove('display-none');
                            alertMsg.classList.add('failed');
                            alertMsg.innerHTML = obj.data.message;
                        }
        
                    } )
                    .catch( err => console.log(err) );
        
                });

            }


        }else if(cqfs_admin_obj.action === 'add'){
            /**
             * run on add new page
             */
            [enableBtn, disableBtn, emailUser].map( v => {
                v.disabled = true;
                v.addEventListener('click', (e) => e.preventDefault() );
            } );
            
            email_metabox.setAttribute("style", "display: none;");

        }

        //set the isSubmit var to true when submitting
        form.addEventListener('submit', e => {
            isSubmit = true;
        });

        // event listner for window object `beforeunload`
        const initForm = storeFormData( form );
        window.addEventListener("beforeunload", function (e) {

            // new form data store before unload event
            const newForm = storeFormData( form );

            // bail early if form is submitting
            if( isSubmit ){
                return;
            }

            // finally check if form data changes are there.
            if( initForm != newForm ){
                e.preventDefault();
                return e.returnValue = '';
            }

        });

        
    }// endif `cqfs_entry`

    /**
     * run only for cqfs settings page in admin
     * base `toplevel_page_cqfs-settings` from $screen in meta-boxes.php
     */
    if( cqfs_admin_obj.base === 'toplevel_page_cqfs-settings' ){

        // mail settings form
        const form_mail_settings = document.querySelector('form[name=cqfs-mail-settings]');
        // recreate result page form
        const form_recreate_resultpage = document.querySelector('form[name="recreate-result-page-form"]');

        if( form_recreate_resultpage ){

            // main container
            const container = document.querySelector('.result-page-status');
            const loader = form_recreate_resultpage.querySelector('.cqfs-loader');
            const submitBtn = form_recreate_resultpage.querySelector('input[type="submit"]');

            form_recreate_resultpage.addEventListener('submit', e => {
                // prevent default
                e.preventDefault();
                loader.classList.remove('display-none');
                submitBtn.disabled = true;
                const formData = new FormData(e.target);
                formData.append('ajax_request', 1);

                postData( cqfs_admin_obj.ajax_url, formData )
                .then(response => response.json() )
                .then( obj => {
                    
                    if( obj.success ){
                        e.target.remove();
                        loader.classList.add('display-none');
                        container.innerHTML = obj.data.message;
                    }

                    if( !obj.success ){
                        loader.classList.add('display-none');
                        container.innerHTML = obj.data.message;
                    }
    
                })
                .catch( err => console.log(err) );

            });
        }

        form_mail_settings.addEventListener('submit', (e) => {

            //set submit is true
            isSubmit = true;
        });

        // event listner for window object `beforeunload`
        const initForm = storeFormData( form_mail_settings );
        window.addEventListener("beforeunload", function (e) {

            // new form data store before unload event
            const newForm = storeFormData( form_mail_settings );

            // bail early if form is submitting
            if( isSubmit ){
                return;
            }

            // finally check if form data changes are there.
            if( initForm != newForm ){
                e.preventDefault();
                return e.returnValue = '';
            }

        });

    }    

})();