<?php


class AkMailComposer extends AkObject 
{
    var $Mail;
    var $composed_message = '';
    
    function setMail(&$Mail)
    {
        $this->Mail =& $Mail;
    }
    
    function getComposedMessage()
    {
        $raw_message = '';
        if(empty($this->parts)){
            if(!empty($Mail->_isPart)){
                $raw_message .= $Mail->getRawPart();
            }else{
                $raw_message .= $Mail->getRawHeaders().AK_ACTION_MAILER_EOL.AK_ACTION_MAILER_EOL.$Mail->getRawBody();
            }
        }else{
            $boundary = $Mail->getBoundary();

            $Mail->content_type_attributes['boundary'] = $boundary;
            $raw_message .= $Mail->getRawHeaders();

            foreach (array_keys($Mail->parts) as $k){
                $raw_message .= AK_ACTION_MAILER_EOL.AK_ACTION_MAILER_EOL.'--'.$boundary.AK_ACTION_MAILER_EOL.$Mail->parts[$k]->getRawMessage();
            }

            $raw_message .= AK_ACTION_MAILER_EOL.'--'.$boundary.'--'.AK_ACTION_MAILER_EOL;
        }

        //_propagateMultipartParts

        return $raw_message;
    }
    
    
    
    function getMultipartMessage()
    {
        $raw_message = '';
        $boundary = $this->getBoundary();

        $this->content_type_attributes['boundary'] = $boundary;
        $this->_skip_adding_date_to_headers = true;
        $raw_message .= $this->getRawHeaders();

        foreach (array_keys($this->parts) as $k){
            $raw_message .= AK_ACTION_MAILER_EOL.AK_ACTION_MAILER_EOL.'--'.$boundary.AK_ACTION_MAILER_EOL.$this->parts[$k]->getRawMessage();
        }

        $raw_message .= AK_ACTION_MAILER_EOL.'--'.$boundary.'--'.AK_ACTION_MAILER_EOL;

    }

    function getBoundary()
    {
        return 'mimepart_'.Ak::randomString(8).'..'.Ak::randomString(8);
    }

    function getRawPart()
    {
        return $this->getRawHeaders().AK_ACTION_MAILER_EOL.AK_ACTION_MAILER_EOL.$this->getRawBody();
    }

    function getRawHeaders()
    {
        $this->_prepareHeadersForRendering();
        return $this->_getHeadersAsText();
    }
}

?>