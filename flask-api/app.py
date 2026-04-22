from flask import Flask, jsonify, request
from fpdf import FPDF
import pandas as pd
import os

app = Flask(__name__)

@app.route('/ping', methods=['GET'])
def ping():
    return jsonify({"status": "ok", "message": "Flask API corriendo"})


# ----------------------------
# ANALYZE - Endpoint principal
# ----------------------------
@app.route('/analyze', methods=['POST'])
def analyze():
    data = request.get_json()

    # Validar que llegó el campo filepath
    if not data or 'filepath' not in data:
        return jsonify({"error": "Se requiere el campo 'filepath'"}), 400

    filepath = data['filepath']

    # Validar que el archivo existe
    if not os.path.exists(filepath):
        return jsonify({"error": f"Archivo no encontrado: {filepath}"}), 404

    try:
        # Leer el CSV con Pandas
        df = pd.read_csv(filepath)

        # Info general del archivo
        total_rows    = len(df)
        total_columns = len(df.columns)

        # Separar columnas numéricas y de texto
        numeric_cols = df.select_dtypes(include='number').columns.tolist()
        text_cols    = df.select_dtypes(exclude='number').columns.tolist()

        # Calcular estadísticas por columna numérica
        column_stats = []
        for col in numeric_cols:
            column_stats.append({
                "column"  : col,
                "min"     : round(float(df[col].min()), 2),
                "max"     : round(float(df[col].max()), 2),
                "mean"    : round(float(df[col].mean()), 2),
                "median"  : round(float(df[col].median()), 2),
                "std"     : round(float(df[col].std()), 2),
                "nulls"   : int(df[col].isnull().sum()),
                "valid"   : int(df[col].notnull().sum())
            })

        # Construir respuesta final
        response = {
            "status"         : "ok",
            "total_rows"     : total_rows,
            "total_columns"  : total_columns,
            "numeric_columns": numeric_cols,
            "text_columns"   : text_cols,
            "stats"          : column_stats
        }

        return jsonify(response), 200

    except Exception as e:
        return jsonify({"error": f"Error al procesar el archivo: {str(e)}"}), 500
# ----------------------------
# EXPORT PDF - Genera reporte PDF
# ----------------------------
@app.route('/export-pdf', methods=['POST'])
def export_pdf():
    data = request.get_json()

    if not data or 'stats' not in data:
        return jsonify({"error": "Datos insuficientes"}), 400

    try:
        stats        = data['stats']
        upload_id    = data.get('upload_id', 0)
        total_rows   = data.get('total_rows', 0)
        total_cols   = data.get('total_columns', 0)

        pdf = FPDF()
        pdf.add_page()

        # Título
        pdf.set_font('Helvetica', 'B', 18)
        pdf.cell(0, 12, 'CSV Data Explorer - Reporte de Analisis', ln=True)

        # Línea separadora
        pdf.set_draw_color(200, 200, 200)
        pdf.line(10, pdf.get_y(), 200, pdf.get_y())
        pdf.ln(4)

        # Resumen general
        pdf.set_font('Helvetica', 'B', 11)
        pdf.cell(0, 8, 'Resumen general', ln=True)
        pdf.set_font('Helvetica', '', 10)
        pdf.cell(0, 7, f'ID de analisis: #{upload_id}', ln=True)
        pdf.cell(0, 7, f'Total de filas: {total_rows}', ln=True)
        pdf.cell(0, 7, f'Total de columnas: {total_cols}', ln=True)
        pdf.ln(4)

        # Tabla de estadísticas
        pdf.set_font('Helvetica', 'B', 11)
        pdf.cell(0, 8, 'Estadisticas por columna numerica', ln=True)
        pdf.ln(2)

        # Encabezados de tabla
        headers = ['Columna', 'Min', 'Max', 'Promedio', 'Mediana', 'Nulos']
        widths  = [40, 22, 22, 28, 28, 22]

        pdf.set_fill_color(240, 240, 240)
        pdf.set_font('Helvetica', 'B', 9)
        for i, header in enumerate(headers):
            pdf.cell(widths[i], 8, header, border=1, fill=True)
        pdf.ln()

        # Filas de datos
        pdf.set_font('Helvetica', '', 9)
        for col in stats:
            pdf.cell(widths[0], 7, str(col['column'])[:18], border=1)
            pdf.cell(widths[1], 7, str(col['min']),         border=1)
            pdf.cell(widths[2], 7, str(col['max']),         border=1)
            pdf.cell(widths[3], 7, str(col['mean']),        border=1)
            pdf.cell(widths[4], 7, str(col['median']),      border=1)
            pdf.cell(widths[5], 7, str(col['nulls']),       border=1)
            pdf.ln()

        # Footer
        pdf.ln(8)
        pdf.set_font('Helvetica', 'I', 8)
        pdf.set_text_color(150, 150, 150)
        pdf.cell(0, 6, 'Generado con CSV Data Explorer - github.com/TU_USUARIO/csv-data-explorer', ln=True)

        return pdf.output(), 200, {
            'Content-Type': 'application/pdf'
        }

    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True, port=5000)