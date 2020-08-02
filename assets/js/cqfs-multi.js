(function(){
/**
 * This script handles the CQFS form validation and submission
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
        console.log(all_dup);

        uniq.push(all_dup[0]);
    }

    console.log(uniq);
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

    return returnVal;

}

/**
 * Validated the cqfs_question inputs in cqfs form shortcode
 * 
 * @param {cqfs instance} cqfs 
 * @param {event} event 
 */
function form_input_validation( cqfs, event ){

    //return value
    let returnVal = true;

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
            event.preventDefault();
            returnVal = false;
        }

    });

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

        html += `<h3 class="cqfs-uname">Hello ${obj.data.user_title}</h3>`;
        html += `<div class="cqfs-pass-msg">
        <p class="cqfs-percentage">${obj.data.percentage}&#37; correct.</p>
        <p>${obj.data.remarks}</p></div>`;

        html += obj.data.all_questions.map( q => {
            return `
            <div class="cqfs-entry-qa">
                <h4>${q.question}</h4>
                <p><label>${_cqfs_lang.you_ans}</label>${q.answer}</p>
                <p><label>${_cqfs_lang.status}</label>${q.status}</p>
                <details><summary>${_cqfs_lang.note}</summary><p>${q.note}</p></details>
            </div>
            `;
        }).join('');

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
function formSubmitEvent(e, processingDiv, cqfs){

    //form data for ajax submit
    const formData = new FormData(e.target);
    //validate form inputs
    let inpValidation = form_input_validation( cqfs, e );

    //run for not logged in users
    //validate the name and email field
    if( ! _cqfs.login_status ){

        //for ajax submit mode
        if( !_cqfs.form_handle || _cqfs.form_handle === 'ajax_mode' ){
            e.preventDefault();
            let emailValidation = form_name_email_validation( cqfs, e );

            if( inpValidation && emailValidation ){
                showMe( processingDiv );
                postData( _cqfs.ajaxurl, formData )
                .then(response => response.json() )
                .then( obj => {
                    hideMe( processingDiv );
                    e.target.remove();
                    cqfs.insertAdjacentHTML( 'beforeend', afterResponse( obj ));
                    console.log(obj);
                } );
                
            }
        }
        
        //for php submit mode
        form_name_email_validation( cqfs, e );

    }else if( !_cqfs.form_handle || _cqfs.form_handle === 'ajax_mode' ){

        //ajax submit

        if( inpValidation ){
            e.preventDefault();
            showMe( processingDiv );
            postData( _cqfs.ajaxurl, formData )
            .then(response => response.json() )
            .then( obj => {
                hideMe( processingDiv );
                e.target.remove();
                cqfs.insertAdjacentHTML( 'beforeend', afterResponse( obj ));
                console.log(obj);
            } );
            
        }

    }

}

/********************** end of utility functions ***********************/

/**
 * Main node array for multi page layout
 * 
 * @param {node array} cqfs 
 */

let initialize_CqfsMulti = function( cqfs ){
    //code start for the cqfs instance

    //check the layout type
    const layoutType = cqfs.getAttribute("data-cqfs-layout");

    if( layoutType === 'multi' ){

        //run if layout is a multi page

        //container div for questions
        const userForm = cqfs.querySelector('.cqfs-user-form');

        //processing overlay div
        const processingDiv = cqfs.querySelector('.cqfs--processing');

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
        if(userForm){
            questions.push(userForm);
        }
        
        //Select and store answer sets for each question
        const allOptions = questions.map( q => Array.from(q.querySelectorAll('input')));

        console.log(allOptions);

        //event listner for each answer option
        questions.forEach( (q, i, arr) => {

            //for each sets of answer options except the last set
            //enable next button
            if( i != arr.length -1 ){
                q.addEventListener('click', (e) => {
                    //store checked properties with bollean val
                    let checked = allOptions[i].map( v => v.checked );
                    //check if atleast one is checked = true
                    if( checked.includes(true) ){
                        enableMe(nxt);
                    }else{
                        disableMe(nxt);
                    }
                } );
            }

            //for the last set of answers option
            //disable the next button and enable the submit button
            if( i == arr.length -1 ){
                q.addEventListener('click', (e) => {
                    let checked = allOptions[i].map( v => v.checked || v.type === 'text' || v.type === 'email' );
                    console.log(checked);
                    if( checked.includes(true) ){
                        enableMe(submit);
                    }else{
                        disableMe(submit);
                    }
                } );
            }

        });

        /**
         * Add event listners to the following buttons
         * 1. Next button
         * 2. Previous button
         * 3. Submit button
         */
        nxt.addEventListener('click', next);
        prv.addEventListener('click', prev);

        //Add a counter variable for navigation
        let count = 0;

        /**
         * @callback function `next`
         * @param {event} e 
         */
        function next(e){

            //prevent default
            e.preventDefault();

            //disble target
            disableMe(e.target);

            //enable previous button
            enableMe(prv);

            //hide previous question-answer set
            hideMe( questions[count] );

            //show next question-answer set
            showMe( questions[count].nextElementSibling );

            //increament counter
            count++;

            //check if count is last element
            if( count == questions.length -1 ){
                //disable target which is next button
                disableMe(e.target);

                //enable previous button
                enableMe(prv);
            }

            //console.log(count)

        }

        /**
         * @callback function `prev`
         * @param {event} e 
         */
        function prev(e){

            //prevent default
            e.preventDefault();

            //enable next button
            enableMe(nxt);

            //disable submit button
            disableMe(submit);

            //hide next question-answer set
            hideMe( questions[count] );

            //show previous question-answe set
            showMe( questions[count].previousElementSibling );

            //decrement counter
            count--;

            //check if count is first element
            if( count == 0 ){
                //disable target which is previous button
                disableMe(e.target);

                //enable next button
                enableMe(nxt);
            }

            //console.log(count)
            
        }

        /**
         * Form submit event
         */
        form.addEventListener( 'submit', e => formSubmitEvent(e, processingDiv, cqfs) );

    }//layout check


}

/********************* end of initialize_CqfsMulti ********************/

/**
 * cqfs single page forms
 */

let initialize_CqfsSingle = function ( cqfs ){

    //processing div, default hide
    const processingDiv = cqfs.querySelector('.cqfs--processing');
    
    //check the layout type
    const layoutType = cqfs.getAttribute("data-cqfs-layout");

    if( layoutType === 'single' ){
        //run if layout is a single page

        //the form
        const form = cqfs.querySelector('form');

        //form submit validation
        form.addEventListener( 'submit', e => formSubmitEvent(e, processingDiv, cqfs) );

    }

}

document.addEventListener('DOMContentLoaded', () => {

    //multi page instances
    const cqfs_MultiPage = Array.from(document.querySelectorAll('.cqfs.multi'));
    const multiPageInstances = unique_build( cqfs_MultiPage );//retruns unique

    //initialize the cqfs multi page block
    multiPageInstances.forEach( cqfs => {
        initialize_CqfsMulti( cqfs );
    });


    //single page instances
    const cqfs_SinglePage = Array.from(document.querySelectorAll('.cqfs.single'));
    const singlePageInstances = unique_build( cqfs_SinglePage );//retruns unique

    //initialize the cqfs single page block
    singlePageInstances.forEach( cqfs => {
        initialize_CqfsSingle( cqfs );
    });

})


})();//end of main function wrapper