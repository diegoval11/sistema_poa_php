import openpyxl

wb = openpyxl.load_workbook('/home/paladin/Desktop/work/alcaldia-2.0/sistema-poa/temp/15. Gerencia de Planificación y Operaciones, llenado hasta septiembre.xlsx', data_only=False)
sheet = wb['POA COMPLETO'] if 'POA COMPLETO' in wb.sheetnames else wb.active

# Looking for "ACTIVIDADES NO PLANIFICADAS" row
unplan_row = 0
for r in range(15, 50):
    val = sheet[f"C{r}"].value
    if val and "NO PLANIFICADAS" in str(val):
        unplan_row = r
        break

if unplan_row:
    print(f"Unplanned header is at row: {unplan_row}")
    for col in ['S', 'AF', 'AG', 'AT', 'BG', 'BH', 'BI']:
        cell = sheet[f"{col}{unplan_row}"]
        print(f"{col}{unplan_row}: {cell.value}")
        
    print(f"Row 11 {col}: {sheet[f'{col}11'].value}")
    
else:
    print("Not found")

