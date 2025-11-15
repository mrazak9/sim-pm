import { ref } from 'vue';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';

export function useDashboardExport() {
  const exporting = ref(false);
  const exportProgress = ref(0);
  const exportError = ref(null);

  /**
   * Export dashboard to PDF
   * @param {HTMLElement|string} element - Element to export or selector
   * @param {Object} options - Export options
   */
  const exportToPDF = async (element, options = {}) => {
    exporting.value = true;
    exportProgress.value = 0;
    exportError.value = null;

    try {
      // Get element
      const el = typeof element === 'string'
        ? document.querySelector(element)
        : element;

      if (!el) {
        throw new Error('Element not found');
      }

      exportProgress.value = 20;

      // Default options
      const defaultOptions = {
        filename: `dashboard-${new Date().toISOString().split('T')[0]}.pdf`,
        format: 'a4',
        orientation: 'portrait',
        title: 'Dashboard Report',
        addHeader: true,
        addFooter: true,
        scale: 2 // Higher scale for better quality
      };

      const config = { ...defaultOptions, ...options };

      exportProgress.value = 40;

      // Capture element as canvas
      const canvas = await html2canvas(el, {
        scale: config.scale,
        useCORS: true,
        logging: false,
        backgroundColor: '#ffffff'
      });

      exportProgress.value = 60;

      // Calculate PDF dimensions
      const imgWidth = config.orientation === 'portrait' ? 210 : 297; // A4 width in mm
      const imgHeight = (canvas.height * imgWidth) / canvas.width;

      // Create PDF
      const pdf = new jsPDF({
        orientation: config.orientation,
        unit: 'mm',
        format: config.format
      });

      exportProgress.value = 80;

      // Add header
      if (config.addHeader) {
        pdf.setFontSize(16);
        pdf.setTextColor(31, 41, 55); // gray-800
        pdf.text(config.title, 15, 15);

        pdf.setFontSize(10);
        pdf.setTextColor(107, 114, 128); // gray-500
        pdf.text(`Generated: ${new Date().toLocaleString('id-ID')}`, 15, 22);

        // Line separator
        pdf.setDrawColor(229, 231, 235); // gray-200
        pdf.line(15, 25, imgWidth - 15, 25);
      }

      // Add image
      const imgY = config.addHeader ? 30 : 15;
      const imgData = canvas.toDataURL('image/png');
      pdf.addImage(imgData, 'PNG', 15, imgY, imgWidth - 30, imgHeight);

      // Add footer
      if (config.addFooter) {
        const pageHeight = config.orientation === 'portrait' ? 297 : 210;
        pdf.setFontSize(8);
        pdf.setTextColor(156, 163, 175); // gray-400
        pdf.text(
          `SIM-PM Dashboard Report - Page 1`,
          imgWidth / 2,
          pageHeight - 10,
          { align: 'center' }
        );
      }

      exportProgress.value = 95;

      // Save PDF
      pdf.save(config.filename);

      exportProgress.value = 100;

      return {
        success: true,
        filename: config.filename
      };
    } catch (error) {
      console.error('Export error:', error);
      exportError.value = error.message || 'Failed to export dashboard';
      return {
        success: false,
        error: exportError.value
      };
    } finally {
      exporting.value = false;
      setTimeout(() => {
        exportProgress.value = 0;
      }, 1000);
    }
  };

  /**
   * Export specific widgets to PDF
   * @param {Array<HTMLElement|string>} widgets - Array of widgets to export
   * @param {Object} options - Export options
   */
  const exportWidgetsToPDF = async (widgets, options = {}) => {
    exporting.value = true;
    exportProgress.value = 0;
    exportError.value = null;

    try {
      const defaultOptions = {
        filename: `widgets-${new Date().toISOString().split('T')[0]}.pdf`,
        format: 'a4',
        orientation: 'portrait',
        title: 'Widget Report',
        margin: 15
      };

      const config = { ...defaultOptions, ...options };

      const pdf = new jsPDF({
        orientation: config.orientation,
        unit: 'mm',
        format: config.format
      });

      const pageWidth = config.orientation === 'portrait' ? 210 : 297;
      const pageHeight = config.orientation === 'portrait' ? 297 : 210;
      const margin = config.margin;
      let currentY = margin + 20;

      // Add title
      pdf.setFontSize(16);
      pdf.text(config.title, margin, margin + 10);

      pdf.setFontSize(10);
      pdf.setTextColor(107, 114, 128);
      pdf.text(`Generated: ${new Date().toLocaleString('id-ID')}`, margin, margin + 16);

      exportProgress.value = 20;

      for (let i = 0; i < widgets.length; i++) {
        const widget = typeof widgets[i] === 'string'
          ? document.querySelector(widgets[i])
          : widgets[i];

        if (!widget) continue;

        const canvas = await html2canvas(widget, {
          scale: 2,
          useCORS: true,
          logging: false,
          backgroundColor: '#ffffff'
        });

        const imgWidth = pageWidth - (2 * margin);
        const imgHeight = (canvas.height * imgWidth) / canvas.width;

        // Check if we need a new page
        if (currentY + imgHeight > pageHeight - margin) {
          pdf.addPage();
          currentY = margin;
        }

        const imgData = canvas.toDataURL('image/png');
        pdf.addImage(imgData, 'PNG', margin, currentY, imgWidth, imgHeight);

        currentY += imgHeight + 10; // Add spacing between widgets

        exportProgress.value = 20 + ((i + 1) / widgets.length) * 70;
      }

      exportProgress.value = 95;

      // Save PDF
      pdf.save(config.filename);

      exportProgress.value = 100;

      return {
        success: true,
        filename: config.filename
      };
    } catch (error) {
      console.error('Export error:', error);
      exportError.value = error.message || 'Failed to export widgets';
      return {
        success: false,
        error: exportError.value
      };
    } finally {
      exporting.value = false;
      setTimeout(() => {
        exportProgress.value = 0;
      }, 1000);
    }
  };

  /**
   * Export data table to PDF
   * @param {Array} data - Table data
   * @param {Array} columns - Table columns
   * @param {Object} options - Export options
   */
  const exportTableToPDF = (data, columns, options = {}) => {
    const defaultOptions = {
      filename: `table-${new Date().toISOString().split('T')[0]}.pdf`,
      format: 'a4',
      orientation: 'landscape',
      title: 'Data Table'
    };

    const config = { ...defaultOptions, ...options };

    const pdf = new jsPDF({
      orientation: config.orientation,
      unit: 'mm',
      format: config.format
    });

    // Add title
    pdf.setFontSize(16);
    pdf.text(config.title, 15, 15);

    pdf.setFontSize(10);
    pdf.setTextColor(107, 114, 128);
    pdf.text(`Generated: ${new Date().toLocaleString('id-ID')}`, 15, 22);

    // Add table using autoTable plugin (if available)
    if (pdf.autoTable) {
      pdf.autoTable({
        startY: 30,
        head: [columns.map(col => col.header || col.field)],
        body: data.map(row => columns.map(col => row[col.field] || '')),
        theme: 'grid',
        styles: { fontSize: 9 },
        headStyles: { fillColor: [59, 130, 246] } // blue-600
      });
    }

    pdf.save(config.filename);

    return {
      success: true,
      filename: config.filename
    };
  };

  /**
   * Export chart to image
   * @param {HTMLCanvasElement} canvas - Chart canvas element
   * @param {Object} options - Export options
   */
  const exportChartToImage = (canvas, options = {}) => {
    const defaultOptions = {
      filename: `chart-${new Date().toISOString().split('T')[0]}.png`,
      format: 'png',
      quality: 0.95
    };

    const config = { ...defaultOptions, ...options };

    const dataURL = canvas.toDataURL(`image/${config.format}`, config.quality);

    // Create download link
    const link = document.createElement('a');
    link.download = config.filename;
    link.href = dataURL;
    link.click();

    return {
      success: true,
      filename: config.filename
    };
  };

  return {
    exporting,
    exportProgress,
    exportError,
    exportToPDF,
    exportWidgetsToPDF,
    exportTableToPDF,
    exportChartToImage
  };
}
