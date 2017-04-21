USE `Ratemanagement3`;

UPDATE tblEmailTemplate SET  TemplateBody = REPLACE(TemplateBody, '<!--[if !mso]> <!-->', '<!--[if !mso]><!-- ><![endif]-->') where TemplateBody like '%<!--[if !mso]> <!-->%';


