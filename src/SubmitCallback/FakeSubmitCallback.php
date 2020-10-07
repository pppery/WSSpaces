<?php


namespace WSS\SubmitCallback;


class FakeSubmitCallback implements SubmitCallback {
    /**
     * Called upon submitting a form.
     *
     * @param array $form_data The data submitted via the form.
     * @return string|bool
     */
    public function onSubmit( array $form_data ) {
        return false;
    }
}