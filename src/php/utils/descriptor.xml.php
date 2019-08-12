<application xmlns="https://online.moysklad.ru/xml/ns/appstore/app/v1"
             xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             xsi:schemaLocation="https://online.moysklad.ru/xml/ns/appstore/app/v1 https://online.moysklad.ru/xml/ns/appstore/app/v1/application-1.0.0.xsd">
    <iframe>
        <sourceUrl><?=cfg()->appBaseUrl?>/iframe.php</sourceUrl>
    </iframe>
    <vendorApi>
        <endpointBase><?=cfg()->appBaseUrl?>/vendor-endpoint.php</endpointBase>
    </vendorApi>
    <access>
        <resource><?=cfg()->moyskladJsonApiEndpointUrl?></resource>
        <scope>admin</scope>
    </access>
</application>