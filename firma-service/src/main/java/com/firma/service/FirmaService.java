package com.firma.service;

import com.firma.util.MarkerFinder;
import com.itextpdf.awt.geom.Rectangle2D;
import com.itextpdf.text.Rectangle;
import com.itextpdf.text.pdf.*;
import com.itextpdf.text.pdf.parser.PdfReaderContentParser;
import com.itextpdf.text.pdf.security.*;

import org.springframework.stereotype.Service;

import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.security.KeyStore;
import java.security.PrivateKey;
import java.security.cert.Certificate;

@Service
public class FirmaService {

    public void firmar(
            String src,
            String dest,
            String keystore,
            String password,
            String marcador
    ) throws Exception {

        PdfReader reader = null;
        PdfStamper stamper = null;

        try {

            // Certificado
            KeyStore ks = KeyStore.getInstance("PKCS12");

            try (FileInputStream ksStream = new FileInputStream(keystore)) {
                ks.load(ksStream, password.toCharArray());
            }

            String alias = ks.aliases().nextElement();

            PrivateKey pk =
                    (PrivateKey) ks.getKey(alias, password.toCharArray());

            Certificate[] chain =
                    ks.getCertificateChain(alias);

            // PDF
            reader = new PdfReader(src);

            FileOutputStream os = new FileOutputStream(dest);

            stamper = PdfStamper.createSignature(
                    reader,
                    os,
                    '\0',
                    null,
                    true
            );

            PdfSignatureAppearance appearance =
                    stamper.getSignatureAppearance();

            appearance.setReason("Firma digital");
            appearance.setLocation("Ecuador");

            AcroFields fields = reader.getAcroFields();

            String nombreFirma = "sig1";
            int i = 1;

            while (fields.getField(nombreFirma) != null) {
                i++;
                nombreFirma = "sig" + i;
            }

            AcroFields.FieldPosition pos =
                    buscarMarcador(reader, marcador);

            if (pos == null) {
                throw new RuntimeException(
                        "No se encontró el marcador: " + marcador
                );
            }

            appearance.setVisibleSignature(
                    pos.position,
                    pos.page,
                    nombreFirma
            );

            ExternalSignature pks =
                    new PrivateKeySignature(pk, "SHA256", null);

            ExternalDigest digest =
                    new BouncyCastleDigest();

            MakeSignature.signDetached(
                    appearance,
                    digest,
                    pks,
                    chain,
                    null,
                    null,
                    null,
                    0,
                    MakeSignature.CryptoStandard.CMS
            );

        } finally {

            if (stamper != null) {
                stamper.close();
            }

            if (reader != null) {
                reader.close();
            }
        }
    }

    private AcroFields.FieldPosition buscarMarcador(
            PdfReader reader,
            String marcador
    ) throws Exception {

        PdfReaderContentParser parser =
                new PdfReaderContentParser(reader);

        for (int pagina = 1;
             pagina <= reader.getNumberOfPages();
             pagina++) {

            MarkerFinder finder =
                    new MarkerFinder(marcador);

            parser.processContent(pagina, finder);

            Rectangle2D.Float rect = finder.getRect();

            if (rect != null) {

                AcroFields.FieldPosition pos =
                        new AcroFields.FieldPosition();

                pos.page = pagina;

                float anchoFirma = 150;
                float centroMarcador = rect.x + rect.width / 2;

                pos.position = new Rectangle(
                        centroMarcador - anchoFirma / 2,
                        rect.y,
                        centroMarcador + anchoFirma / 2,
                        rect.y + 50
                );

                return pos;
            }
        }

        return null;
    }

}