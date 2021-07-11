# Rabble Datatable Bundle
The Rabble datatable bundle is required for most features within the Rabble admin interface.
It allows you to create datatables using only backend code.

# Installation
Install the bundle by running
```sh
composer require rabble/datatable-bundle
```

Add the following class to your `config/bundles.php` file:
```php
return [
    ...
    Rabble\DatatableBundle\RabbleDatatableBundle::class => ['all' => true],
]
```

# Usage
Creating a datatable:
```php
<?php

namespace App\Datatable;

use Rabble\DatatableBundle\Datatable\AbstractGenericDatatable;
use Rabble\DatatableBundle\Datatable\Row\Data\Column\Action\Action;
use Rabble\DatatableBundle\Datatable\Row\Data\Column\ActionDataColumn;
use Rabble\DatatableBundle\Datatable\Row\Data\Column\GenericDataColumn;
use Rabble\DatatableBundle\Datatable\Row\Heading\Column\GenericHeadingColumn;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserDatatable extends AbstractGenericDatatable
{
    private AuthorizationCheckerInterface $checker;

    public function __construct(AuthorizationCheckerInterface $checker)
    {
        $this->checker = $checker;
    }

    protected function initialize(): void
    {
        if (!$this->checker->isGranted('user.view')) {
            throw new AccessDeniedHttpException();
        }
        $this->headingColumns = [
            new GenericHeadingColumn('', false, ['style' => ['width' => 60], 'data-sortable' => 'false']),
            new GenericHeadingColumn('table.user.username', 'RabbleUserBundle'),
            new GenericHeadingColumn('table.user.first_name', 'RabbleUserBundle'),
            new GenericHeadingColumn('table.user.last_name', 'RabbleUserBundle'),
        ];
        $this->dataColumns = [
            new ActionDataColumn([
                'actions' => [
                    new Action(
                        'Routing.generate("rabble_admin_user_view", {user: data.getId()})',
                        'eye'
                    ),
                    new Action(
                        'Routing.generate("rabble_admin_user_edit", {user: data.getId()})',
                        'pencil',
                        '(is_granted(data) || is_granted("role.overrule")) && is_granted("user.edit")'
                    ),
                    new Action(
                        'Routing.generate("rabble_admin_user_delete", {user: data.getId()})',
                        'trash',
                        '(is_granted(data) || is_granted("role.overrule")) && is_granted("user.delete") && data !== get_user()',
                        [
                            'class' => 'btn-danger',
                            'data-confirm' => '?Translator.trans("user.delete_confirm", [], "RabbleUserBundle")',
                            'data-reload-datatable' => $this->getName(),
                        ]
                    ),
                ],
            ]),
            new GenericDataColumn([
                'expression' => 'data.getUsername()',
                'searchField' => 'username',
                'sortField' => 'username',
            ]),
            new GenericDataColumn([
                'expression' => 'data.getFirstName()',
                'searchField' => 'firstName',
                'sortField' => 'firstName',
            ]),
            new GenericDataColumn([
                'expression' => 'data.getLastName()',
                'searchField' => 'lastName',
                'sortField' => 'lastName',
            ]),
        ];
    }
}
```
The above example is included within the Rabble user bundle. As you can see, you can use the Symfony expression language to resolve the data passed from the data fetcher.

Now finally, we need to register the datatable to the service container.

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
    <services>
        <service id="App\Datatable\UserDatatable">
            <argument type="service" id="security.authorization_checker"/>
            <tag name="rabble_datatable" data_source="User" data_fetcher="@rabble.datatable.data_fetcher.orm" />
        </service>
    </services>
</container>
```

Using this service, we can simply call the following function in the Twig template:

```twig
{{ datatable('user') }}
```

And the datatable will get rendered! Of course, you do need to include the javascript for datatables to work. Check https://datatables.net/ to learn how to do this. 