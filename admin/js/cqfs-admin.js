(function(){
/**
 * JavaScript for the admin screens
 */
    "use strict";

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