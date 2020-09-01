<?php

namespace wangzhan\upload\oss\Result;

use wangzhan\upload\oss\Model\GetLiveChannelStatus;

class GetLiveChannelStatusResult extends Result
{
    /**
     * @return
     */
    protected function parseDataFromResponse()
    {
        $content = $this->rawResponse->body;
        $channelList = new GetLiveChannelStatus();
        $channelList->parseFromXml($content);
        return $channelList;
    }
}
