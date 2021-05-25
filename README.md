# Form controllers

Streamlined controllers handling form input.

## Usage

### Annotate controller

Keep creating and handling the form input out of your controller by annotating it with the desired [FormType](https://symfony.com/doc/current/forms.html#creating-form-classes).

```php
namespace App\Controller;

use App\Form\MyFormType;
use Pitch\Form\Form;

class MyController
{
    #[Form(MyFormType::class)]
    public function __invoke($data)
    {
        // Just handle the data of a valid form.
        // If the form is not submitted yet or the input is invalid,
        // the controller will not be called and the
        // Symfony\Component\Form\FormInstance will be returned.
    }
}
```

This also supports [Doctrine Annotations](https://github.com/doctrine/annotations/) if installed.

### Handle `FormInstance` controller return

Symfony requires controllers to return a `Symfony\Component\HttpFoundation\Response`.
But you can convert other return values (like the `FormInstance`) on the `kernel.view` event.
Add your own [EventSubscriber](https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-subscriber)
or [add a ResponseHandler with pitch/symfony-adr](https://github.com/ph-fritsche/symfony-adr#turn-controller-into-action).

### Use the form inside the controller

Just like the data for valid forms the `FormInstance` is made available to the controller per [Request attributes])https://symfony.com/doc/current/components/http_foundation.html#accessing-request-data).
[Symfony's RequestAttributeValueResolver](https://symfony.com/doc/current/controller/argument_value_resolver.html#built-in-value-resolvers) injects them into the controller if there is a parameter with the same name as the attribute.
The attribute names default to just `data` and `form`, but in case of conflicts you could provide others per annotation.

```php
namespace App\Controller;

use App\Form\MyFormType;
use Pitch\Form\Form;
use Symfony\Component\Form\FormInterface;

class MyController
{
    #[Form(
        MyFormType::class,
        dataAttr: 'myData',
        formAttr: 'myForm',
    )]
    public function __invoke(
        Request $request,
        FormInterface $myForm,
        $myData,
    ) {
    }
}
```

You can prevent the automatic return of invalid or unsubmitted forms per annotation `#[Form(MyFormType::class, returnForm: false)]` or per [config parameter](https://symfony.com/doc/current/configuration.html#configuration-parameters) `pitch_form.returnForm: false`.

### Use data entities

```php
namespace App\Controller;

use App\Form\MyFormType;
use App\Form\MyFormEntity;
use Pitch\Form\Form;

class MyController
{
    #[Form(
        MyFormType::class,
        entity: MyFormEntity::class,
    )]
    public function __invoke(MyFormEntity $data)
    {
    }
}
```

If the entity can not be created by just calling the constructor, you can register a factory implementing `Pitch\Form\FormEntityFactoryInterface` as a service.

```php
namespace App\Controller;

use App\Form\MyFormType;
use App\Form\MyFormEntity;
use Pitch\Form\Form;

class MyController
{
    #[Form(
        MyFormType::class,
        entityFactory: 'myFactoryId',
    )]
    public function __invoke(MyFormEntity $data)
    {
    }
}
```
