#FragmentCache

The Fragment Cache Bundle aims to store in cache some Symfony Sub Requests, for example those that you embed in your twigs via

```
{{ render(controller('AcmeDemoBundle:Index:footer') }}
```

These sub requests may be expensive to execute, and may always return the same information, so let's cache it!

How? With an annotation

```
@FragmentCache()
```