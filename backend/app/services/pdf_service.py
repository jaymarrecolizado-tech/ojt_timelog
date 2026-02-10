from reportlab.lib import colors
from reportlab.lib.pagesizes import A4, portrait
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch
from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from io import BytesIO
from datetime import date
from typing import Optional


class DTRGenerator:
    def __init__(self):
        self.page_width, self.page_height = portrait(A4)
        self.styles = getSampleStyleSheet()
        self._setup_styles()

    def _setup_styles(self):
        self.title_style = ParagraphStyle(
            "Title",
            parent=self.styles["Heading1"],
            fontSize=14,
            alignment=TA_CENTER,
            spaceAfter=12,
        )
        self.subtitle_style = ParagraphStyle(
            "Subtitle",
            parent=self.styles["Normal"],
            fontSize=10,
            alignment=TA_CENTER,
            spaceAfter=6,
        )
        self.info_style = ParagraphStyle(
            "Info",
            parent=self.styles["Normal"],
            fontSize=10,
            alignment=TA_LEFT,
            spaceAfter=3,
        )
        self.footer_style = ParagraphStyle(
            "Footer",
            parent=self.styles["Normal"],
            fontSize=9,
            alignment=TA_LEFT,
            spaceBefore=20,
        )

    def generate_dtr_pdf(
        self, student_info: dict, period: str, rows: list[dict], totals: dict
    ) -> bytes:
        buffer = BytesIO()
        doc = SimpleDocTemplate(
            buffer,
            pagesize=portrait(A4),
            leftMargin=0.5 * inch,
            rightMargin=0.5 * inch,
            topMargin=0.5 * inch,
            bottomMargin=0.5 * inch,
        )
        elements = []

        elements.append(Paragraph("DAILY TIME RECORD", self.title_style))
        elements.append(Paragraph(period, self.subtitle_style))
        elements.append(Spacer(1, 12))

        info_data = [
            f"<b>Name:</b> {student_info.get('full_name', 'N/A')}",
            f"<b>Student ID:</b> {student_info.get('student_id_no', 'N/A')}",
            f"<b>Department:</b> {student_info.get('department', 'N/A')}",
            f"<b>Program:</b> {student_info.get('program', 'N/A')}",
            f"<b>Company:</b> {student_info.get('company', 'N/A') or 'N/A'}",
            f"<b>OJT Period:</b> {student_info.get('ojt_period', 'N/A')}",
        ]
        for info in info_data:
            elements.append(Paragraph(info, self.info_style))

        elements.append(Spacer(1, 12))

        table_header = [
            "Date",
            "AM In",
            "AM Out",
            "PM In",
            "PM Out",
            "Hours",
            "Remarks",
        ]
        table_data = [table_header]

        for row in rows:
            table_data.append(
                [
                    row.get("date", "")[:15],
                    row.get("am_in", ""),
                    row.get("am_out", ""),
                    row.get("pm_in", ""),
                    row.get("pm_out", ""),
                    row.get("hours_rendered", ""),
                    row.get("remarks", ""),
                ]
            )

        col_widths = [
            1.3 * inch,
            0.6 * inch,
            0.6 * inch,
            0.6 * inch,
            0.6 * inch,
            0.5 * inch,
            0.8 * inch,
        ]
        table = Table(table_data, colWidths=col_widths, repeatRows=1)
        table.setStyle(
            TableStyle(
                [
                    ("BACKGROUND", (0, 0), (-1, 0), colors.grey),
                    ("TEXTCOLOR", (0, 0), (-1, 0), colors.whitesmoke),
                    ("ALIGN", (0, 0), (-1, -1), "CENTER"),
                    ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
                    ("FONTSIZE", (0, 0), (-1, 0), 8),
                    ("FONTSIZE", (0, 1), (-1, -1), 7),
                    ("BOTTOMPADDING", (0, 0), (-1, 0), 8),
                    ("BACKGROUND", (0, 1), (-1, -1), colors.white),
                    ("GRID", (0, 0), (-1, -1), 0.5, colors.black),
                    ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
                ]
            )
        )

        elements.append(table)
        elements.append(Spacer(1, 12))

        totals_data = [
            f"<b>Monthly Hours:</b> {totals.get('monthly_hours', 0)}",
            f"<b>Accumulated Hours:</b> {totals.get('accumulated_hours', 0)} / {totals.get('required_hours', 0)}",
            f"<b>Remaining Hours:</b> {totals.get('remaining_hours', 0)}",
            f"<b>Completion:</b> {totals.get('completion_percentage', 0)}%",
        ]
        for t in totals_data:
            elements.append(Paragraph(t, self.info_style))

        elements.append(Spacer(1, 30))

        signature_style = ParagraphStyle(
            "Sig", parent=self.styles["Normal"], fontSize=9
        )
        elements.append(
            Paragraph("Certified Correct: _________________________", signature_style)
        )
        elements.append(Spacer(1, 8))
        elements.append(
            Paragraph("Verified By: _________________________", signature_style)
        )
        elements.append(Spacer(1, 8))
        elements.append(
            Paragraph("Noted By: _________________________", signature_style)
        )

        doc.build(elements)
        buffer.seek(0)
        return buffer.read()


dtr_generator = DTRGenerator()
