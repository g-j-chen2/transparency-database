# DSA Transparency Database FAQ

_Frequently Asked Questions and Answers_

<h2 class="ecl-u-type-heading-2">General FAQ</h2>

<x-ecl.accordion label="What is the DSA Transparency Database?">
Article 17 of the Digital Services Act (DSA) requires all providers of hosting services to provide clear
and specific information, called statements of reasons, to users whenever they remove or otherwise restrict
access to their content.<br />
<br />
Additionally, Article 24 (5) of the DSA requires providers of online platforms, which are a type of hosting
service, to send all their statements of reasons to the Commission’s
<a href="https://transparency.dsa.ec.europa.eu/">DSA Transparency Database</a> for collection.
The database is publicly accessible and machine-readable.
</x-ecl.accordion>

<x-ecl.accordion label="What is a hosting service? What is an online platform?">
Hosting services include a broad range of online intermediaries, for example cloud and webhosting services.
These services store information provided by, and at the request of, users.<br />
<br />
The DSA Transparency Database only collects statements of reasons from online platforms, a subset of hosting
services. Online platforms, such as online marketplaces, app stores, or social networks, not only store
information provided by users but also disseminate it publicly. That is, they make it available to potentially
all users of an online platform.
</x-ecl.accordion>

<x-ecl.accordion label="What is a statement of reasons?">
A statement of reasons is an important tool to empower users to understand and potentially challenge content
moderation decisions taken by providers of hosting services.<br />
<br />
As specified in Article 17 of the DSA, a statement of reasons is the information that providers of hosting
services, including online platforms, are required to share with a user whenever they remove or otherwise
restrict access to their content. Restrictions can be imposed on the grounds that the content is illegal or
incompatible with the terms and conditions of the provider.<br />
<br />
Information contained in a statement of reasons includes, amongst other things, the type of restriction put in
place, the grounds relied upon and the facts and circumstances around which the content moderation decision
was taken.<br />
<br />
The statements of reasons that providers of online platforms are required to submit to the DSA Transparency
Database must contain this information.
</x-ecl.accordion>

<x-ecl.accordion label="Is there any part of a statement of reasons that is not published in the DSA Transparency
Database?">
Providers of online platforms are obliged to remove any personal data from the information they publish in the
DSA Transparency Database, in accordance with Article 24(5) of the DSA. In case personal data is included in any
of the statements of reasons, the Commission can be notified using the ‘Report an issue’ button.<br />
<br />
Redress options are also not included in the DSA Transparency Database as those are relevant only for the addressee
of the statement of reasons. In any event would be identical in all cases:<br />
<br />
<ul>
  <li>internal complaint mechanism under Article 20 of the DSA,</li>
  <li>out-of-court dispute settlement under Article 21 of the DSA,</li>
  <li>judicial review under the relevant national laws.</li>
</ul>
</x-ecl.accordion>

<x-ecl.accordion label="Why was the DSA Transparency Database created?">
The DSA Transparency Database was created in line with Article 24(5) of the DSA to enable more transparency and
scrutiny over the content moderation decisions taken by providers of online platforms, and to better monitor the
spread of illegal and harmful content online.
</x-ecl.accordion>

<x-ecl.accordion label="Who can use the DSA Transparency Database?">
The DSA Transparency Database is publicly accessible. It allows people to
<a href="https://transparency.dsa.ec.europa.eu/statement-search">search for</a>, read and download statements of
reasons. For an interactive overview over the statements of reasons contained in the DSA Transparency Database,
visit the Analytics of the DSA Transparency Database.
</x-ecl.accordion>

<x-ecl.accordion label="Where can I find information about the data points included in the DSA Transparency Database?">
For more information about the data included in the DSA Transparency Database, please visit the
<a href="https://transparency.dsa.ec.europa.eu/page/documentation">Documentation page</a>.
It explains what type of information is collected, and how the different data fields map onto Article 17 of the DSA,
which lays down the information required in a statement of reasons.
</x-ecl.accordion>

<x-ecl.accordion label="Where can I find more information about the DSA?">
The DSA is a comprehensive set of new rules that regulate the responsibilities of digital services.
<a href="https://commission.europa.eu/strategy-and-policy/priorities-2019-2024/europe-fit-digital-age/digital-services-act_en">
Find out more about the DSA</a>.
</x-ecl.accordion>

<x-ecl.accordion label="I would like to give feedback – how can I do that?">
Please use the feedback form. To use the <a href="https://transparency.dsa.ec.europa.eu/feedback">feedback form</a>,
you need to create an EU Login account.
</x-ecl.accordion>

<h2 class="ecl-u-type-heading-2">Technical FAQ</h2>
<x-ecl.accordion label="I would like to extract a large number of statements of reasons from the DSA Transparency
Database. How do I do that?">
The <a href="https://transparency.dsa.ec.europa.eu/data-download">Data Download</a> page of the DSA Transparency
Database contains all submitted statements of reasons organised into daily zip files. It is possible to download
zip files containing the submissions of all online platforms, or to select the zip files containing the statements of
reasons of each individual online platform. The files can be filtered through a dropdown menu in the top right corner.
The files are provided in full and light versions. The full version contains all data fields of each statement of
reasons (<a href="https://transparency.dsa.ec.europa.eu/page/api-documentation">see the full database schema</a>),
whereas the light version does not contain free text attributes with a
character limit higher than 2000 characters (i.e. _illegal_content_explanation_, _incompatible_content_explanation_ or
_decision_facts_). Light archive files also do not contain the _territorial_scope_ attribute.
</x-ecl.accordion>

<x-ecl.accordion label="I would like to sample data from the DSA Transparency Database. How do I do that?">
To obtain a sample of submissions to the DSA Transparency Database, you can use the .csv file download link available
above the table displaying the results of a search for statements of reasons.<br />
<br />
By default, the latest 1000 results will be available for download. To adapt the content of the sample, you can specify
search parameters in the advanced search page. The first 1000 results from your advanced search will then be available
for .csv file download.
</x-ecl.accordion>

<x-ecl.accordion label="I would like to get access to the content for which a statement of reasons was created.
How do I do that?">
The DSA Transparency Database only records statement of reasons. These contain information on the content moderation
decision itself as well as the information accompanying such decisions, with the exception of personal data, which
providers of online platforms are required to remove before submission. The DSA Transparency Database does not contain
the content that was subject to moderation.<br />
<br />
For researchers interested in gaining access to the content underlying certain statements of reasons, the data access
mechanism specified in Article 40 of the DSA can provide a way to obtain such access in the future.<br />
<br />
Once the Digital Service Coordinators are established by 17 February 2024, data access requests can be submitted either
to the Digital Service Coordinator of a researcher’s Member State or to the Digital Service Coordinator(s) where the
provider of the online platform(s) in question is established. The Commission is currently drafting a Delegated Act that
will lay down technical and procedural requirements of the Article 40 data access mechanism.
</x-ecl.accordion>

<h2 class="ecl-u-type-heading-2">Platform FAQ</h2>

<x-ecl.accordion label="I am responsible for implementing Article 24(5) of the DSA as a provider of an online
platform. What steps do I have to go through?">

To set up your statement of reasons submission process, please contact the Digital Service Coordinator of your Member
State. This is the first step required to be onboarded as an online platform with obligations under Article 24(5) of the
DSA.<br />
<br />
Once you are onboarded via your Digital Service Coordinator, you will gain access to a sandbox environment to test your
submissions to the DSA Transparency Database, which you can perform either via an Application Programming Interface (
API) or a webform, according to the volume of your data and technical needs.<br />
<br />
Once the testing phase is completed, you will be able to move to the production environment of the DSA Transparency
Database, where you can start submitting your statement of reasons via an API or a webform.
</x-ecl.accordion>

<x-ecl.accordion label="What are the technical options for sending statements of reasons to the DSA
Transparency Database?">
Statements of reasons can be submitted either via an API or a webform. For more information about the API, please
consider the <a href="https://transparency.dsa.ec.europa.eu/page/api-documentation">API documentation</a>. The data
schema of the web form is the same as the data schema of the API.
</x-ecl.accordion>

<x-ecl.accordion label="Where can I find the data schema with all statement of reasons attributes used in the DSA
Transparency Database?">
All attributes, which are part of a statement of reasons submission to the DSA Transparency Database, are detailed in
the <a href="https://transparency.dsa.ec.europa.eu/page/api-documentation">API documentation</a>. The data schema of the
web form is the same as the data schema of the API.
</x-ecl.accordion>

<x-ecl.accordion label="What are the API endpoint options for the DSA Transparency Database and which one would you
recommend for sending statements of reasons at a very high volume?">
The DSA Transparency database has two API endpoints, one which allows to submit one statement of reasons per call and
one which allows to submit from 1 to 100 statements of reasons per call. For more information on the API endpoints,
please read the <a href="https://transparency.dsa.ec.europa.eu/page/api-documentation">API documentation</a>.<br />
<br />
For high-volume submissions of multiple statements of reasons per minute, we recommend using the batch API endpoint.
</x-ecl.accordion>

<x-ecl.accordion label="Where do I find information on error codes?">
For detailed information on possible error codes, please read the relevant section in
the <a href="https://transparency.dsa.ec.europa.eu/page/api-documentation">API documentation</a>.
</x-ecl.accordion>