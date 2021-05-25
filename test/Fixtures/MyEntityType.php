<?php
namespace Pitch\Form\Fixtures;

use DateTimeInterface;

class MyEntityType
{
    public string $someText;
    public DateTimeInterface $someDate;

    protected int $protectedProp;
}
