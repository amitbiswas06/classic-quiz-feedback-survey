(function(){
/**
 * front end shortcode forms
 * This script handles the CQFS shortcode form validation and submission
 * @since 1.0.0
 */

"use strict";

/**********************************************************
 * Utility functions -
 * disableMe()
 * enableMe()
 * showMe()
 * hideMe()
 * unique_build()
 * validateInput()
 * form_name_email_validation()
 * form_input_validation()
 * postData()
 * afterResponse()
 * formSubmitEvent()
 * 
 * Available object
 * _cqfs (see page source for keys)
 * _cqfs_lang (for strings use in js)
**********************************************************/

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
 * Disable input, buttons etc
 * 
 * @param {Node Element} el 
 */
function disableMe(el){
    if( el.disabled == false ){
        el.disabled = true;
        el.classList.add('disabled');
    }
}

/**
 * Enable input, buttons etc
 * 
 * @param {Node Element} el 
 */
function enableMe(el){
    if( el.disabled == true ){
        el.disabled = false;
        el.classList.remove('disabled');
    }
}

/**
 * Display Block
 * 
 * @param {Node Element} el 
 */
function showMe(el){
    if( el.classList.contains('hide') ){
        el.classList.remove('hide');
        el.classList.add('show');
    }
}

/**
 * Display None
 * 
 * @param {Node Element} el 
 */
function hideMe(el){
    if( el.classList.contains('show') ){
        el.classList.remove('show');
        el.classList.add('hide');
    }
}

/**
 * Slice array into chunks for pagination
 * 
 * @param {Array} arr 
 * @param {Number} size 
 */
function cqfs_chunk(arr, size) {
    // This prevents infinite loops
    if ( parseInt(size) < 1 ) {
        throw new Error('Size must be positive');
    }
  
    let result = [];
    for ( let i = 0; i < arr.length; i += parseInt(size) ) {
        result.push( arr.slice(i, i + parseInt(size)) );
    }

    return result;

}


/**
 * Remove CQFS shortcode objects with same ID and keeps the first entry
 * 
 * @param {Array} cqfs_NodeArray The main node array of CQFS shortcode object
 */
function unique_build( cqfs_NodeArray ){

    const pageUniq = cqfs_NodeArray.map( node => node.getAttribute('id'))
    .filter( (val, idx, arr) => arr.indexOf(val) === idx );

    let uniq = [];
    for(let i = 0; i < pageUniq.length; i++){
        let all_dup = [];
        for (let j = 0; j < cqfs_NodeArray.length; j++){
            if( cqfs_NodeArray[j].getAttribute('id') === pageUniq[i] ){
                all_dup.push(cqfs_NodeArray[j]);
            }
        }

        for( let k = 1; k < all_dup.length; k++ ){
            all_dup[k].remove();
        }

        uniq.push(all_dup[0]);
    }

    return uniq;

}


/**
 * Input validation check
 * 
 * @param {array} arr array of input nodes
 */
function validateInput( arr ){
    let value = false;
    if( arr.some( val => val.checked ) ){
        value = true;
    }
    return value;
}

/**
 * Validates the name and email field for not logged in user
 * 
 * @param {cqfs instance} cqfs 
 */
function form_name_email_validation( cqfs, event ){

    //return value
    let returnVal = true;

    //name and email field for non logged in user
    const form_uname = cqfs.querySelector('input[name="_cqfs_uname"]');
    const invalid_name_msg = cqfs.querySelector('.error-uname');
    const form_email = cqfs.querySelector('input[name="_cqfs_email"]');
    const invalid_email_msg = cqfs.querySelector('.error-email');

    //regex
    const letters = /^[A-Za-z\s]+$/;
    const emailID = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

    if( form_uname && form_email ){

        [form_uname, form_email].map( v => v.classList.remove('cqfs-error') );
        [invalid_name_msg, invalid_email_msg].map( v => hideMe(v) );

        if( !form_uname.value || !form_uname.value.match(letters) || form_uname.value.length < 3 || form_uname.value.length > 25 ){
            form_uname.classList.add('cqfs-error');
            showMe(invalid_name_msg);
            event.preventDefault();
            returnVal = false;
        }

        if ( ! emailID.test(form_email.value) ){
            form_email.classList.add('cqfs-error');
            showMe(invalid_email_msg);
            event.preventDefault();
            returnVal = false;
        }

    }

    return returnVal;

}

/**
 * Validated the cqfs_question inputs in cqfs form shortcode
 * 
 * @param {cqfs instance} cqfs 
 * @param {event} event 
 */
function form_input_validation( event, cqfs ){

    const data_required = cqfs.getAttribute("data-cqfs-required");

    // req message div
    const req_msg = cqfs.querySelector('.cqfs-error-msg');

    hideMe(req_msg);

    //return value
    let returnVal = true;

    if( data_required == true ){

        //option sets
        const form_options_div = Array.from( cqfs.querySelectorAll('form .question .options') );
        const form_options = form_options_div.map( node => node.querySelectorAll('input') ).map( inp => Array.from(inp) );

        form_options_div.forEach( (opt, idx) => {

            const val = validateInput(form_options[idx]);
            opt.classList.remove('cqfs-error');

            //validation run for input fields and prevent submit
            if( ! val ){
                opt.classList.add('cqfs-error');
                opt.scrollIntoView({behavior: "smooth", block: "end", inline: "nearest"});
                showMe(req_msg);
                event.preventDefault();
                returnVal = false;
            }

        });

    }

    return returnVal;

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
 * Display results on ajax submit for the quiz
 * 
 * @param {object} obj That returns after the ajax submission
 */
function afterResponse( obj ){

    let html = '';
    //run on success and if quiz
    if( obj.success && obj.data.form_type === 'quiz' ){

        html += '<div class="cqfs-entry-remarks">';
        html += `<h3 class="cqfs-uname">Hello ${obj.data.user_title}</h3>`;
        html += `<div class="cqfs-msg">
        <p class="cqfs-percentage">${obj.data.percentage}&#37; correct.</p>
        <p class="cqfs-remark">${obj.data.remarks}</p></div>`;
        html += '</div><div class="cqfs-entry-qa">';

        html += obj.data.all_questions.map( q => {
            return `
            <div class="cqfs-entry-qa__single">
                <h4>${q.question}</h4>
                <p><label>${_cqfs_lang.you_ans}</label>${q.answer}</p>
                <p><label>${_cqfs_lang.status}</label>${q.status}</p>
                <details><summary>${_cqfs_lang.note}</summary><p>${q.note}</p></details>
            </div>
            `;
        }).join('');

        html += "</div>";

    }else if( obj.success && obj.data.form_type !== 'quiz' ){
        //run if success and not quiz
        html = `<div class="cqfs-results"><h4>${_cqfs_lang.thank_msg}</h4></div>`;
    }else{
        //run all other case
        html = `<div class="cqfs-invalid-result">${_cqfs_lang.invalid_result}</div>`;
    }

    return html;

}

/**
 * Submit function for both php and ajax mode
 * and also for multi layout and single layout
 * 
 * @param {event}   e               Event that is passed by default
 * @param {node}    processingDiv   Processing div which is hidden default and shown while processing
 * @param {node}    cqfs            Main CQFS node
 */
function formSubmitEvent(e, cqfs){

    //processing overlay div
    const processingDiv = cqfs.querySelector('.cqfs--processing');

    // loading animation div
    const loader = cqfs.querySelector('.cqfs-loader');

    // submit button
    const submitBtn = cqfs.querySelector('.cqfs--submit');

    // ajax submission mode
    const ajax = cqfs.getAttribute("data-cqfs-ajax");

    // guest mode status
    const guest = cqfs.getAttribute("data-cqfs-guest");

    //form data for ajax submit
    const formData = new FormData(e.target);

    //validate form inputs
    let inpValidation = form_input_validation(e, cqfs);

    // logged in class
    const loginClass = cqfs.classList.contains('cqfs-logged-in');

    //run for not logged in users
    //validate the name and email field
    if( ! loginClass ){

        //for ajax submit mode
        if( ajax == true ){
            e.preventDefault();

            let emailValidation = true;
            if( guest == true ){
                emailValidation = form_name_email_validation( cqfs, e );
            }else{
                emailValidation = false;
                alert('Please login to submit');
            }

            if( inpValidation && emailValidation ){
                showMe(processingDiv);
                showMe(loader);
                submitBtn.disabled = true;
                postData( _cqfs.ajaxurl, formData )
                .then(response => response.json() )
                .then( obj => {
                    hideMe(processingDiv);
                    e.target.remove();
                    cqfs.insertAdjacentHTML( 'beforeend', afterResponse( obj ));
                } );
                
            }
        }else if( guest == true ){
            //for php submit mode
            form_name_email_validation( cqfs, e );
        }else{
            e.preventDefault();
            alert('Please login to submit');
        }
        

    }else if( ajax == true ){

        //ajax submit

        if( inpValidation ){
            e.preventDefault();
            showMe(processingDiv);
            showMe(loader);
            submitBtn.disabled = true;
            postData( _cqfs.ajaxurl, formData )
            .then(response => response.json() )
            .then( obj => {
                hideMe(processingDiv);
                e.target.remove();
                cqfs.insertAdjacentHTML( 'beforeend', afterResponse( obj ));
            } );
            
        }

    }

}

/********************** end of utility functions ***********************/

/**
 * new code for pagination and requirements
 */
let Init_Cqfs = function ( cqfs ){
    
    // data per page status
    const data_perpage = cqfs.getAttribute("data-cqfs-perpage");

    // data layout type status
    const layoutType = cqfs.getAttribute("data-cqfs-layout");

    // questions, nonce, user form container
    const qstContainer = cqfs.querySelector('.cqfs--questions');

    //container div for questions
    const userForm = cqfs.querySelector('.cqfs-user-form');

    //next button
    const nxt = cqfs.querySelector('.cqfs--next');

    //previous button
    const prv = cqfs.querySelector('.cqfs--prev');

    //form
    const form = cqfs.querySelector('form');

    //submit button
    const submit = cqfs.querySelector('.cqfs--submit');

    //Question Object
    let questions = Array.from( cqfs.querySelectorAll('.question') );

    if( layoutType === 'multi' ){

        const qst_chunks = cqfs_chunk(questions, data_perpage);
        let x = qst_chunks.map( towrap => towrap.reduce( (acc, el) => (acc.appendChild(el),acc) , document.createElement('div') ))
        .forEach( (el, i) => {
            el.className = "cqfs-qst-page";
            if( i == 0 ){
                el.classList.add('show');
            }else{
                el.classList.add('hide');
            }

            qstContainer.insertBefore(el, userForm);

        });

        const qst_page = Array.from(cqfs.querySelectorAll('.cqfs-qst-page'));
        qst_page.push(userForm); // push the user form div into the page

        let count = 0;
        nxt.addEventListener('click', e => {
            //prevent default
            e.preventDefault();

            //enable previous button
            enableMe(prv);

            //hide previous question-answer set
            hideMe( qst_page[count] );

            //show next question-answer set
            showMe( qst_page[count].nextElementSibling );

            //increament counter
            count++;

            //check if count is last element
            if( count == qst_page.length -1 ){
                //disable target which is next button
                disableMe(e.target);

                //enable previous button
                enableMe(prv);
                enableMe(submit);
            }

        });

        prv.addEventListener('click', e => {
            //prevent default
            e.preventDefault();

            //enable next button
            enableMe(nxt);

            //disable submit button
            disableMe(submit);

            //hide next question-answer set
            hideMe( qst_page[count] );

            //show previous question-answe set
            showMe( qst_page[count].previousElementSibling );

            //decrement counter
            count--;

            //check if count is first element
            if( count == 0 ){
                //disable target which is previous button
                disableMe(e.target);
                disableMe(submit);

                //enable next button
                enableMe(nxt);
            }

        });

    }

    // form submission
    form.addEventListener('submit', e => formSubmitEvent(e, cqfs) );

}

document.addEventListener('DOMContentLoaded', () => {

    // login modal
    const loginModal = document.getElementById('cqfs-login');
    const openModalLinks = Array.from(document.querySelectorAll('.cqfs-modal-link'));
    const closeBtn = document.querySelector('.cqfs-close');
    const alertMsg = document.querySelector('.cqfs-alert-message');
    const userForms = Array.from(document.querySelectorAll('.cqfs-user-form'));
    const cqfsDivs = Array.from(document.querySelectorAll('.cqfs'));
    const cqfsNonce = Array.from( document.querySelectorAll('input[name^=_cqfs_nonce_]') );
    
    // run if login modal is available
    if( loginModal ){

        const loginForm = loginModal.querySelector('form[name="cqfs-login-form"]');
        const loader = loginModal.querySelector('.cqfs-loader');
        const submitBtn = loginModal.querySelector('button[type="submit"]');

        // When the user clicks the button, open the modal 
        openModalLinks.map( el => el.addEventListener('click', (e) => {
            e.preventDefault();
            fadeIn(loginModal);
        }));

        // When the user clicks on <span> (x), close the modal
        closeBtn.onclick = function() {
            fadeOut(loginModal)
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if ( event.target == loginModal ) {
                fadeOut(loginModal)
            }
        }

        loginForm.addEventListener('submit', (e) => {

            // prevent default
            e.preventDefault();
            loader.classList.remove('display-none');
            submitBtn.disabled = true;
            const formData = new FormData(e.target);
            formData.append('ajax_request', 1);

            postData( _cqfs.ajaxurl, formData )
            .then(response => response.json() )
            .then( obj => {

                // return obj;
                if( obj.success ){
                    e.target.remove();
                    alertMsg.classList.remove('display-none');
                    alertMsg.classList.add('success');
                    alertMsg.innerHTML = obj.data.message;

                    setTimeout( () => {
                        fadeOut(loginModal);
                        userForms.map( el => el.innerHTML = obj.data.status );
                        cqfsDivs.map( el => el.classList.add('cqfs-logged-in'));
                        cqfsNonce.map( inp => inp.value = obj.data.nonce);
                    }, 2000 );

                }

                if( !obj.success ){
                    loader.classList.add('display-none');
                    submitBtn.disabled = false;
                    alertMsg.classList.remove('display-none');
                    alertMsg.classList.add('failure');
                    alertMsg.innerHTML = obj.data.message;
                }

            } )
            .catch( err => console.log(err) );

        });

    }
    

    //cqfs instances
    const CQFS = Array.from(document.querySelectorAll('.cqfs'));
    const cqfsInstances = unique_build( CQFS );//retruns unique

    //initialize the cqfs block
    cqfsInstances.forEach( cqfs => {
        Init_Cqfs( cqfs );
    });

});


})();//end of main function wrapper