<?php

namespace BfwApi\test\run;

class Books extends \BfwApi\Rest
{
    public function getRequest()
    {
        $returnedDatas = (object) [
            'elements' => (object) [
                'elemA' => [
                    0 => (object) [
                        'elemB' => 'Foo',
                        'elemC' => 'Bar'
                    ],
                    1 => (object) [
                        'elemB' => 'Foz',
                        'elemC' => 'Baz'
                    ]
                ]
            ]
        ];
        
        $this->sendResponse($returnedDatas);
    }
}
