<?php

namespace Anytime\ORM\Tests\Stub;

use Anytime\ORM\EntityManager\Entity;

class Foo extends Entity 
{
    const TABLENAME = 'foo_entity';
    const PRIMARY_KEYS = ['id'];

    protected $data = [
	    'id' => 1,
	    'some_field' => ''
    ];

    protected $dataSetterUsed = [
        'id' => false,
        'some_field' => false
    ];

    public function setSomeField($value)
    {
        $this->data['some_field'] = $value;
        $this->dataSetterUsed['some_field'] = true;
    }
};
