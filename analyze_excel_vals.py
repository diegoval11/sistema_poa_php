import openpyxl

wb = openpyxl.load_workbook('/home/paladin/Desktop/work/alcaldia-2.0/sistema-poa/temp/15. Gerencia de Planificación y Operaciones, llenado hasta septiembre.xlsx', data_only=True)
sheet = wb['POA COMPLETO'] if 'POA COMPLETO' in wb.sheetnames else wb.active

for row in range(15, 25):
    # Jan Programmed(G), Realized(H), %(I)
    g = sheet[f"G{row}"].value
    h = sheet[f"H{row}"].value
    i = sheet[f"I{row}"].value
    
    # Trimestral S
    s = sheet[f"S{row}"].value
    
    if g == 0:
        print(f"Row {row}: G={g}, H={h}, I='{i}', Q1(S)='{s}'")

