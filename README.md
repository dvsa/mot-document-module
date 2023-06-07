# Document snapshot and report generation library

This library provides two modules; one to create point-in-time snapshots of
documents and another to generate a report based on a given template and
document identifier. These modules enforce clean if slightly awkward
separation of concerns; the document creation module requires a database
connection while the report service directly invokes lambda Certificate Generation Service via
its RESTful API.

## Modules

* DvsaDocument
* DvsaReport

## Installation

Add the git repository to the `repositories` section of your
application's `composer.json`:

    "repositories": [
        {
            "type": "git",
            "url": "ssh://git@gitlab.clb.npm/sc/dvsa-document-module.git"
        }
    ]

Then simply `require` the relevant
[tag](http://gitlab.clb.npm/sc/dvs-document-module/tags):

    "require": {
        "dvsa/dvsa-document-module": "0.1.0"
    }

## Configuration

### Document Module

The document module does not require any standalone configuration,
although it does depend on doctrine and a database connection being
available to the consumer application.

### Report Module

If you need to generate a report, ensure the following keys are present
somewhere in your application's configuration (i.e. somewhere under
config/autoload or similar):

    'certificate_generation' => [
            'uri' => {lambda_uri},
            'x-api-key' => {labda_api_key},
            'logCalls' => {true or false value}
    ]

## Usage

### Creating a document (DvsaDocument)

The document module does not expose a route to create a new document since
this logic will most often be encompassed as part of an application specific
call. The `DocumentService` exposes a method to do this directly:

    public function createSnapshot($templateName, $data = [])

`templateName` must be a valid 'friendly name' as defined by the consuming
application; this will be looked up against a list of database templates
and stored as a foreign key against the newly created document.

`data` is simply an array of key/value pairs which make up the document's
content.

### Return value

The return value will **always** be a document identifier; if the process
fails an `EmptyDocumentException` or `TemplateNotFoundException` will
be raised.

### Retrieving the report name for a document (DvsaDocument)

The document module exposes a route to look up the relevant jasper
report name for a given document identifier and optional variation,
which in turn invokes the following `DocumentService` method:

    public function getReportName($documentId, $variation = null)

The optional `variation` parameter must correspond to a valid template
variation defined in the application's database. This allows for one
document to be used as the source data for multiple reports whilst still
enforcing that the reports are somehow related; i.e. the same report
translated into a different language.

### Return value

The return value will **always** be a string representing the full
Jasper Report name. If the process fails a `TemplateNotFound` exception
will be raised.

### Route

The above service call is exposed via a single route,
`/get-report-name/:id/:variation` which if successful will return a
JSON object with a single property, `report-name`.

## Retrieving a report (DvsaReport)

Armed with a `$documentId` and a `$reportName`, simply get an instance of
the `ReportService` and invoke the following method:

    public function getReportById($documentId, $reportName, $runtimeParams = [])

This method constructs a URL which is then requested directly via Jasper
Server's RESTful API; as such the consuming application in this instance
must be able to communicate directly with your configured Jasper endpoint.

The optional `runtimeParams` variable will be passed along with the request
allowing your template to accept extra input at report generation time. Each
key of the array will be available within your report as $P{KeyName} which
will have its value replaced accordingly.

### Return value

This method will return a raw \Laminas\Http\Response which you can interrogate for
the relevant document information (size, content type, etc). The response
also stores an over-arching success status, `$response->isSuccess()`.

### Converting a report response into a domain model (DvsaReport)

If the Zend Http Response object isn't suitable, you may inject it into
back into a helper method of the service which will translate it into a
Report model:

    public function getReportFromResponse(\Laminas\Http\Response $response)

### Return value

This will return a Report model which is simply a business layer
representation of the original response. It will throw a
ReportNotFoundException if the response was not successful.

Contributing
------------
Please refer to our [Contribution Guide](/CONTRIBUTING.md) and [Contributor Code of Conduct](/CODE_OF_CONDUCT.md).
