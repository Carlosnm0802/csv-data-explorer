from flask import Flask, jsonify, request
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


if __name__ == '__main__':
    app.run(debug=True, port=5000)