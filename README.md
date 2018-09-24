
# Nova Custom Form
This package allows you to define entirely customizable components for specific Nova Resources.

# Installation 

```
composer require yassi/nova-custom-form
```

Once installed, simply add the CustomFormTrait to your App\Nova\Resource:

<pre>
namespace App\Nova;

use Laravel\Nova\Resource as NovaResource;
use Laravel\Nova\Http\Requests\NovaRequest;
<b>use Yassi\NovaCustomForm\CustomFormTrait;</b>

abstract class Resource extends NovaResource
{
   <b>use CustomFormTrait;</b>
    ...
</pre>

# Create a new form 
This is exactly the same process as for any other Nova Tool, ResourceTool or Field. You can simply use that command in your terminal:

```
php artisan nova:form @vendor/package-name
```

This will create your form component in /nova-components/PackageName. If you've installed the dependencies during the previous process, you can directly go ahead and use:

```
cd /nova-components/PackageName && yarn watch
```

You can modify the Create and Edit components inside <b>/nova-components/PackageName/resources/js/components</b> as you like :)

# Attach a new form to a resource
The CustomFormTrait adds a static method "form" to your resources and passes the current request as an argument. By default, that function returns null, meaning it will use the default Nova form. You just need to override that function and return a new instance of your custom form.

<pre>
namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\Password;
<b>use Vendor\PackageName\PackageName;</b>

class User extends Resource
{
    ...
    <b>
    /**
     * This method registers the custom form
     * to be used for the User resource.
     * 
     * @return PackageName
     */
    public static function form ($request) {
        return new PackageName;
    }
    </b>
</pre>

You can specify which user or which type of user get access to this custom form:

```
public static function form ($request) {
    return $request->user()->email === 'superadmin@email.fr' ? new PackageName : null;
}
```
