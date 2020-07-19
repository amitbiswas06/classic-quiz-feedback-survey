(function(){
/**
 * This script handles the multi page layout of the CQFS form
 * @since 1.0.0
 */

"use strict";

/**********************************************************
 * Utility functions -
 * disableMe()
 * enableMe()
 * showMe()
 * hideMe()
 * quizRemarks()
**********************************************************/

/**
 * Disable input, buttons etc
 * @param {Node Element} el 
 */
function disableMe(el){
    if( el.disabled == false ){
        el.disabled = true,
        el.classList.add('disabled')
    }
}

/**
 * Enable input, buttons etc
 * @param {Node Element} el 
 */
function enableMe(el){
    if( el.disabled == true ){
        el.disabled = false,
        el.classList.remove('disabled')
    }
}

/**
 * Display Block
 * @param {Node Element} el 
 */
function showMe(el){
    if( el.classList.contains('hide') ){
        el.classList.remove('hide')
        el.classList.add('show')
    }
}

/**
 * Display None
 * @param {Node Element} el 
 */
function hideMe(el){
    if( el.classList.contains('show') ){
        el.classList.remove('show')
        el.classList.add('hide')
    }
}

//multi page instances
const cqfs_MultiPage = document.querySelectorAll('.cqfs.multi');

let initialize_CqfsMulti = function( cqfs ){
    //code start for the cqfs instance

    //container div for questions
    const questionsContainer = cqfs.querySelector('.cqfs--questions');

    //processing overlay div
    const processingDiv = cqfs.querySelector('.cqfs--processing');

    //next button
    const nxt = cqfs.querySelector('.cqfs--next');

    //previous button
    const prv = cqfs.querySelector('.cqfs--prev');

    //submit button
    const submit = cqfs.querySelector('.cqfs--submit');

    //Question Object
    const questions = Array.from( cqfs.querySelectorAll('.question') );
    
    //Select and store answer sets for each question
    const allOptions = questions.map( q => Array.from(q.querySelectorAll('input')));

    console.log(allOptions);

    //event listner for each answer option
    questions.forEach( (q, i, arr) => {

        //for each sets of answer options except the last set
        //enable next button
        if( i != arr.length -1 ){
            q.addEventListener('click', (e) => {
                let checked = allOptions[i].map( v => v.checked );
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
                let checked = allOptions[i].map( v => v.checked );
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
    nxt.addEventListener('click', next)
    prv.addEventListener('click', prev)
    // submit.addEventListener('click', submission)

    //Add a counter variable for navigation
    let count = 0

    /**
     * @callback function `next`
     * @param {event} e 
     */
    function next(e){

        //prevent default
        e.preventDefault()

        //disble target
        disableMe(e.target)

        //enable previous button
        enableMe(prv)

        //hide previous question-answer set
        hideMe( questions[count] )

        //show next question-answer set
        showMe( questions[count].nextElementSibling )

        //increament counter
        count++

        //check if count is last element
        if( count == questions.length -1 ){
            //disable target which is next button
            disableMe(e.target)

            //enable previous button
            enableMe(prv)
        }

        //console.log(count)

    }

    /**
     * @callback function `prev`
     * @param {event} e 
     */
    function prev(e){

        //prevent default
        e.preventDefault()

        //enable next button
        enableMe(nxt)

        //disable submit button
        disableMe(submit)

        //hide next question-answer set
        hideMe( questions[count] )

        //show previous question-answe set
        showMe( questions[count].previousElementSibling )

        //decrement counter
        count--

        //check if count is first element
        if( count == 0 ){
            //disable target which is previous button
            disableMe(e.target)

            //enable next button
            enableMe(nxt)
        }

        //console.log(count)
        
    }
    


}

//initialize the cqfs block
cqfs_MultiPage.forEach( cqfs => {
    initialize_CqfsMulti( cqfs );
})

})();
