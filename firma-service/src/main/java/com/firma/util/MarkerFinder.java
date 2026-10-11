package com.firma.util;

import com.itextpdf.awt.geom.Rectangle2D;
import com.itextpdf.text.pdf.parser.*;

public class MarkerFinder implements RenderListener {

    private final String marker;
    private Rectangle2D.Float rect;

    public MarkerFinder(String marker) {
        this.marker = marker;
    }

    @Override
    public void beginTextBlock() {
    }

    @Override
    public void endTextBlock() {
    }

    @Override
    public void renderImage(ImageRenderInfo imageRenderInfo) {
    }

    @Override
    public void renderText(TextRenderInfo renderInfo) {

        String text = renderInfo.getText();

        if (text != null && text.contains(marker)) {

            Vector start = renderInfo.getBaseline().getStartPoint();
            Vector end = renderInfo.getBaseline().getEndPoint();

            float x = start.get(Vector.I1);
            float y = start.get(Vector.I2);
            float width = end.get(Vector.I1) - x;

            rect = new Rectangle2D.Float(
                    x,
                    y,
                    width,
                    50
            );
        }
    }

    public Rectangle2D.Float getRect() {
        return rect;
    }

}