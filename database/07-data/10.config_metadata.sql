INSERT INTO config_metadata VALUES ('executor.module.path.bands-extractor', 'BandsExtractor Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.sample-selection', 'Sample Selection Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('processor.l3a.weight.aot.minweight', 'Minimum weight depending on AOT', 'float', true, 3);
INSERT INTO config_metadata VALUES ('executor.module.path.xml-statistics', 'XML Statistics', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.temporal-resampling', 'Temporal Resampling Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.feature-extraction', 'Feature Extraction Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.compute-images-statistics', 'Compute Images Statistics Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.train-images-classifier', 'Train Images Classifier Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('http-listener.root-path', 'Document Root Path', 'directory', true, 12);
INSERT INTO config_metadata VALUES ('http-listener.listen-port', 'Dashboard Listen Port', 'int', true, 12);
INSERT INTO config_metadata VALUES ('executor.module.path.image-classifier', 'Image Classifier Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.compute-confusion-matrix', 'Compute Confusion Matrix Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('crop-type.classifier', 'Classifier Type', 'string', true, 6);
INSERT INTO config_metadata VALUES ('crop-type.classifier.rf.nbtrees', 'Maximum Trees in the Forest', 'int', true, 6);
INSERT INTO config_metadata VALUES ('crop-type.classifier.rf.min', 'Minimum Samples in Node', 'int', true, 6);
INSERT INTO config_metadata VALUES ('crop-type.classifier.rf.max', 'Maximum Tree Depth', 'int', true, 6);
INSERT INTO config_metadata VALUES ('crop-type.classifier.svm.k', 'SVM Kernel Type', 'string', true, 6);
INSERT INTO config_metadata VALUES ('crop-type.classifier.svm.opt', 'SVM Parameter Optimization', 'bool', true, 6);
INSERT INTO config_metadata VALUES ('crop-type.classifier.field', 'Field Name', 'string', true, 6);
INSERT INTO config_metadata VALUES ('processor.l3a.weight.aot.maxweight', 'Maximum weight depending on AOT', 'float', true, 3);
INSERT INTO config_metadata VALUES ('crop-type.sampling-rate', 'Temporal Interpolation Sampling Rate (days)', 'int', true, 6);
INSERT INTO config_metadata VALUES ('crop-type.sample-ratio', 'Training/Validation Sample Ratio', 'float', true, 6);
INSERT INTO config_metadata VALUES ('executor.module.path.ogr2ogr', 'ogr2ogr Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.random-selection', 'Random Selection Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.temporal-features', 'Temporal Features Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.statistic-features', 'Statistic Features Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('crop-mask.sampling-rate', 'Temporal Interpolation Sampling Rate (days)', 'int', true, 5);
INSERT INTO config_metadata VALUES ('executor.processor.l2a.name', 'L2A Processor Name', 'string', true, 8);
INSERT INTO config_metadata VALUES ('executor.processor.l3a.name', 'L3A Processor Name', 'string', true, 8);
INSERT INTO config_metadata VALUES ('crop-mask.sample-ratio', 'Training/Validation Sample Ratio', 'float', true, 5);
INSERT INTO config_metadata VALUES ('crop-mask.training-samples-number', 'Training/Validation Sample Ratio', 'float', true, 5);
INSERT INTO config_metadata VALUES ('crop-mask.classifier', 'Classifier Type', 'string', true, 5);
INSERT INTO config_metadata VALUES ('crop-mask.classifier.rf.nbtrees', 'Maximum Trees in the Forest', 'int', true, 5);
INSERT INTO config_metadata VALUES ('executor.processor.l3b.name', 'L3B Processor Name', 'string', true, 8);
INSERT INTO config_metadata VALUES ('crop-mask.classifier.rf.min', 'Minimum Samples in Node', 'int', true, 5);
INSERT INTO config_metadata VALUES ('crop-mask.classifier.rf.max', 'Maximum Tree Depth', 'int', true, 5);
INSERT INTO config_metadata VALUES ('executor.processor.l4a.name', 'L4A Processor Name', 'string', true, 8);
INSERT INTO config_metadata VALUES ('crop-mask.classifier.svm.k', 'SVM Kernel Type', 'string', true, 5);
INSERT INTO config_metadata VALUES ('crop-mask.classifier.svm.opt', 'SVM Parameter Optimization', 'bool', true, 5);
INSERT INTO config_metadata VALUES ('executor.module.path.concatenate-images', 'Concatenate Images Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.processor.l4b.name', 'L4B Processor Name', 'string', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.gdalwarp', 'gdalwarp Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('processor.l3a.weight.cloud.coarseresolution', 'Coarse resolution for quicker convolution', 'int', true, 3);
INSERT INTO config_metadata VALUES ('executor.module.path.dummy-module', 'Dummy module path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('crop-mask.classifier.field', 'Field Name', 'string', true, 5);
INSERT INTO config_metadata VALUES ('resources.gdalwarp.working-mem', 'gdalwarp working memory (MB)', 'int', true, 14);
INSERT INTO config_metadata VALUES ('executor.module.path.color-mapping', 'Color Mapping Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.compression', 'Compression Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('general.scratch-path', 'Path for temporary files', 'string', true, 1);
INSERT INTO config_metadata VALUES ('monitor-agent.disk-path', 'Disk Path To Monitor For Space', 'directory', true, 13);
INSERT INTO config_metadata VALUES ('executor.processor.l2a.path', 'L2A Processor Path', 'file', false, 8);
INSERT INTO config_metadata VALUES ('executor.processor.l3a.path', 'L3A Processor Path', 'file', false, 8);
INSERT INTO config_metadata VALUES ('executor.processor.l3b.path', 'L3B Processor Path', 'file', false, 8);
INSERT INTO config_metadata VALUES ('executor.processor.l4a.path', 'L4A Processor Path', 'file', false, 8);
INSERT INTO config_metadata VALUES ('executor.processor.l4b.path', 'L4B Processor Path', 'file', false, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.product-formatter', 'Product Formatter Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.wrapper-path', 'Processor Wrapper Path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('monitor-agent.scan-interval', 'Measurement Interval (s)', 'int', true, 13);
INSERT INTO config_metadata VALUES ('executor.listen-ip', 'Executor IP Address', 'string', true, 8);
INSERT INTO config_metadata VALUES ('executor.listen-port', 'Executor Port', 'int', true, 8);
INSERT INTO config_metadata VALUES ('executor.shapes_dir', 'Tiles shapes directory', 'directory', true, 8);
INSERT INTO config_metadata VALUES ('archiver.max_age.l2a', 'L2A Product Max Age (days)', 'int', false, 7);
INSERT INTO config_metadata VALUES ('archiver.max_age.l3a', 'L3A Product Max Age (days)', 'int', false, 7);
INSERT INTO config_metadata VALUES ('archiver.max_age.l3b', 'L3B Product Max Age (days)', 'int', false, 7);
INSERT INTO config_metadata VALUES ('archiver.max_age.l4a', 'L4A Product Max Age (days)', 'int', false, 7);
INSERT INTO config_metadata VALUES ('archiver.max_age.l4b', 'L4B Product Max Age (days)', 'int', false, 7);
INSERT INTO config_metadata VALUES ('processor.l3a.weight.aot.maxaot', 'Maximum value of the linear range for weights w.r.t. AOT', 'float', true, 3);
INSERT INTO config_metadata VALUES ('processor.l3a.weight.cloud.sigmasmall', 'Standard deviation of gaussian filter for distance to small clouds', 'float', true, 3);
INSERT INTO config_metadata VALUES ('processor.l3a.weight.cloud.sigmalarge', 'Standard deviation of gaussian filter for distance to large clouds', 'float', true, 3);
INSERT INTO config_metadata VALUES ('processor.l3a.weight.total.weightdatemin', 'Minimum weight at edge of the synthesis time window', 'float', true, 3);
INSERT INTO config_metadata VALUES ('processor.l3a.weight.total.bandsmapping', 'The bands mapping for the master and secondary product types', 'file', true, 3);
INSERT INTO config_metadata VALUES ('executor.module.path.composite-mask-handler', 'Composite Mask Handler path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.composite-preprocessing', 'Composite Mask Handler path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.composite-weigh-aot', 'Composite Mask Handler path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.composite-weigh-on-clouds', 'Composite Mask Handler path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.composite-total-weight', 'Composite Mask Handler path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.composite-update-synthesis', 'Composite Mask Handler path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.composite-splitter', 'Composite Mask Handler path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('processor.l3b.lai.localwnd.bwr', 'Backward radius of the window for the local algorithm', 'int', true, 4);
INSERT INTO config_metadata VALUES ('processor.l3a.bandsmapping', 'Bands mapping file for S2', 'file', true, 3);
INSERT INTO config_metadata VALUES ('processor.l3a.preproc.scatcoeffs_10m', 'Scattering coefficients file for S2 10 m', 'file', true, 3);
INSERT INTO config_metadata VALUES ('processor.l3a.preproc.scatcoeffs_20m', 'Scattering coefficients file for S2 20 m', 'file', true, 3);
INSERT INTO config_metadata VALUES ('processor.l3a.lut_path', 'L3A LUT file path', 'file', true, 3);
INSERT INTO config_metadata VALUES ('processor.l3b.lai.localwnd.fwr', 'Forward radius of the window for the local algorithm', 'int', true, 4);
INSERT INTO config_metadata VALUES ('executor.module.path.lai-ndvi-rvi-extractor', 'LAI NDVI and RVI features extractor', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.lai-models-extractor', 'Determines the model to be used for the current execution', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.lai-bv-image-invertion', 'Applies the regression model to the set of input reflectances', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.lai-bv-err-image-invertion', 'Applies the error regression model to the set of input reflectances', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.lai-time-series-builder', 'Builds a raster with all time series', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.lai-err-time-series-builder', 'Builds a raster with all error images time series', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.lai-local-window-reprocessing', 'Executes the local window reprocessing', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.lai-local-window-reproc-splitter', 'Splits the local window LAI as image for each date', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.lai-fitted-reprocessing', 'Executes the fitted reprocessing', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.lai-fitted-reproc-splitter', 'Splits the fitted LAI as image for each date', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.pheno-ndvi-metrics', 'Phenological NDVI metrics path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.pheno-ndvi-metrics-splitter', 'Phenological NDVI metrics splitter path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.lai-mono-date-mask-flags', 'Extracts the mask flags for the monodate LAI', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.lai-msk-flags-time-series-builder', 'Builds a raster with all masks from the time series', 'file', true, 8);
INSERT INTO config_metadata VALUES ('processor.l3b.lai.modelsfolder', 'Folder where the models are located', 'file', true, 4);
INSERT INTO config_metadata VALUES ('processor.l3b.lai.lut_path', 'L3B LUT file path', 'file', true, 4);
INSERT INTO config_metadata VALUES ('downloader.max-cloud-coverage', 'Maximum Cloud Coverage (%)', 'int', false, 15);
INSERT INTO config_metadata VALUES ('processor.l3b.generate_models', 'Specifies if models should be generated or not for LAI', 'int', true, 4);
INSERT INTO config_metadata VALUES ('processor.l3b.mono_date_lai', 'Specifies if monodate processing should be performed for LAI', 'int', true, 4);
INSERT INTO config_metadata VALUES ('processor.l3b.reprocess', 'Specifies if N-Day reprocessing should be performed for LAI', 'int', true, 4);
INSERT INTO config_metadata VALUES ('processor.l3b.fitted', 'Specifies if fitting reprocessing (end of season) should be performed for LAI', 'int', true, 4);
INSERT INTO config_metadata VALUES ('processor.l3b.production_interval', 'The backward processing interval from the scheduled date for L3B products', 'int', true, 4);
INSERT INTO config_metadata VALUES ('processor.l3b.reproc_production_interval', 'The backward processing interval from the scheduled date for L3C products', 'int', true, 4);
INSERT INTO config_metadata VALUES ('downloader.summer-season.start', 'Summer Season Start', 'string', false, 15);
INSERT INTO config_metadata VALUES ('downloader.summer-season.end', 'Summer Season End', 'string', false, 15);
INSERT INTO config_metadata VALUES ('processor.l4b.crop-mask', 'Crop mask file path or product folder to be used', 'file', true, 6);
INSERT INTO config_metadata VALUES ('site.upload-path', 'Upload path', 'string', true, 17);
INSERT INTO config_metadata VALUES ('executor.module.path.lai-bv-input-variable-generation', 'BV input variables generator', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.lai-prosail-simulator', 'Prosail simulator', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.lai-training-data-generator', 'Training data generator', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.lai-inverse-model-learning', 'Inverse model learning', 'file', true, 8);
INSERT INTO config_metadata VALUES ('processor.l3b.lai.rsrcfgfile', 'L3B RSR file configuration for ProsailSimulator', 'file', true, 4);
INSERT INTO config_metadata VALUES ('archiver.archive_path', 'Archive Path', 'string', false, 7);
INSERT INTO config_metadata VALUES ('executor.module.path.principal-component-analysis', 'Principal component analysis', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.mean-shift-smoothing', 'Mean shift smoothing', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.lsms-segmentation', 'LSMS segmentation', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.lsms-small-regions-merging', 'LSMS small regions merging', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.majority-voting', 'Majority voting', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.image-compression', 'Image compression', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.data-smoothing', 'Data smoothing', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.features-without-insitu', 'Features without insitu', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.compute-image-statistics', 'Compute image statistics', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.erosion', 'Erosion', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.trimming', 'Trimming', 'file', true, 8);
INSERT INTO config_metadata VALUES ('executor.module.path.train-images-classifier-new', 'TrainImagesClassifierNew', 'file', true, 8);
INSERT INTO config_metadata VALUES ('demmaccs.output-path', 'path for l2a products', 'string', false, 16);
INSERT INTO config_metadata VALUES ('demmaccs.gips-path', 'path where the gips files are to be found', 'string', false, 16);
INSERT INTO config_metadata VALUES ('executor.module.path.crop-mask-features-with-insitu', 'CropMask features with insitu path', 'file', true, 8);
INSERT INTO config_metadata VALUES ('processor.l3a.synth_date_sched_offset', 'Difference in days between the scheduled and the synthesis date', 'int', true, 3);
INSERT INTO config_metadata VALUES ('processor.l3a.half_synthesis', 'Half synthesis interval in days', 'int', true, 3);
INSERT INTO config_metadata VALUES ('processor.l3a.generate_20m_s2_resolution', 'Specifies if composite for S2 20M resolution should be generated', 'int', true, 3);
INSERT INTO config_metadata VALUES ('demmaccs.srtm-path', 'path where the srtm files are to be found', 'string', false, 16);
INSERT INTO config_metadata VALUES ('demmaccs.swbd-path', 'path where the swbd files are to be found', 'string', false, 16);
INSERT INTO config_metadata VALUES ('demmaccs.maccs-launcher', 'launcher for maccs within the keeping unit', 'string', false, 16);
INSERT INTO config_metadata VALUES ('demmaccs.working-dir', 'working directory for demmaccs', 'string', false, 16);
INSERT INTO config_metadata VALUES ('processor.l4a.reference_data_dir', 'CropMask folder where insitu data are checked', 'file', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.mission', 'The main mission for the time series', 'string', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.temporal_resampling_mode', 'The temporal resampling mode choices=[resample, gapfill]', 'string', true, 5);
INSERT INTO config_metadata VALUES ('downloader.s2.write-dir', 'Write directory for Sentinel2', 'string', false, 15);
INSERT INTO config_metadata VALUES ('processor.l4a.radius', 'The radius used for gapfilling in days', 'int', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.random_seed', 'The random seed used for training', 'float', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.window', 'The window expressed in number of records used for the temporal features extraction', 'int', true, 5);
INSERT INTO config_metadata VALUES ('downloader.l8.write-dir', 'Write directory for Landsat8', 'string', false, 15);
INSERT INTO config_metadata VALUES ('downloader.winter-season.end', 'Winter Season End', 'string', false, 15);
INSERT INTO config_metadata VALUES ('processor.l4a.smoothing-lambda', 'The lambda parameter used in data smoothing', 'float', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.weight', 'The weight factor for data smoothing', 'float', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.nbcomp', 'The number of components used by dimensionality reduction', 'int', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.segmentation-spatial-radius', 'The spatial radius of the neighborhood used for segmentation', 'int', true, 5);
INSERT INTO config_metadata VALUES ('downloader.winter-season.start', 'Winter Season Start', 'string', false, 15);
INSERT INTO config_metadata VALUES ('processor.l4a.range-radius', 'The range radius defining the radius (expressed in radiometry unit) in the multispectral space', 'float', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.segmentation-minsize', 'Minimum size of a region (in pixel unit) in segmentation.', 'int', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.erode-radius', 'The radius used for erosion', 'int', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.mahalanobis-alpha', 'The parameter alpha used by the mahalanobis function', 'float', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.min-area', 'The minium number of pixel in an area where for an equal number of crop and nocrop samples the crop decision is taken', 'int', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.classifier.field', '', 'string', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.classifier.rf.nbtrees', 'The number of trees used for training', 'int', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.classifier.rf.max', 'maximum depth of the trees used for Random Forest classifier', 'int', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.classifier.rf.min', 'minimum number of samples in each node used by the classifier', 'int', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.sample-ratio', 'The ratio between the validation and training polygons', 'float', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.training-samples-number', 'The number of samples included in the training set', 'int', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.sampling-rate', '', 'int', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.classifier.svm.k', '', 'string', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4a.classifier.svm.opt', '', 'string', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4b.classifier', 'Random forest clasifier / SVM classifier choices=[rf, svm]', 'string', true, 6);
INSERT INTO config_metadata VALUES ('processor.l4a.classifier', 'Random forest clasifier / SVM classifier choices=[rf, svm]', 'string', true, 5);
INSERT INTO config_metadata VALUES ('processor.l4b.classifier.field', 'Training samples feature name', 'string', true, 6);
INSERT INTO config_metadata VALUES ('processor.l4b.classifier.rf.max', 'maximum depth of the trees used for Random Forest classifier', 'int', true, 6);
INSERT INTO config_metadata VALUES ('processor.l4b.classifier.rf.min', 'minimum number of samples in each node used by the classifier', 'int', true, 6);
INSERT INTO config_metadata VALUES ('processor.l4b.classifier.rf.nbtrees', 'The number of trees used for training', 'int', true, 6);
INSERT INTO config_metadata VALUES ('processor.l4b.classifier.svm.k', 'Type of kernel', 'string', true, 6);
INSERT INTO config_metadata VALUES ('processor.l4b.classifier.svm.opt', 'Automatic optimisation of the parameters', 'string', true, 6);
INSERT INTO config_metadata VALUES ('processor.l4b.sample-ratio', 'The ratio between the validation and training polygons', 'float', true, 6);
INSERT INTO config_metadata VALUES ('processor.l4b.sampling-rate', '', 'int', true, 6);
INSERT INTO config_metadata VALUES ('processor.l4b.training-samples-number', 'The number of samples included in the training set', 'int', true, 6);
INSERT INTO config_metadata VALUES ('processor.l4b.temporal_resampling_mode', 'The temporal resampling mode choices=[resample, gapfill]', 'string', true, 6);
INSERT INTO config_metadata VALUES ('processor.l4b.mission', 'The main mission for the time series', 'string', true, 6);
INSERT INTO config_metadata VALUES ('processor.l4b.random_seed', 'The random seed used for training', 'float', true, 6);
INSERT INTO config_metadata VALUES ('downloader.s2.max-retries', 'Maximum retries for downloading a product', 'int', false, 15);
INSERT INTO config_metadata VALUES ('downloader.l8.max-retries', 'Maximum retries for downloading a product', 'int', false, 15);
INSERT INTO config_metadata VALUES ('executor.processor.l3a.keep_job_folders', 'Keep L3A temporary product files for the orchestrator jobs', 'int', true, 8);
INSERT INTO config_metadata VALUES ('executor.processor.l3b.keep_job_folders', 'Keep L3B/C/D temporary product files for the orchestrator jobs', 'int', true, 8);
INSERT INTO config_metadata VALUES ('executor.processor.l3e.keep_job_folders', 'Keep L3E temporary product files for the orchestrator jobs', 'int', true, 8);
INSERT INTO config_metadata VALUES ('executor.processor.l4a.keep_job_folders', 'Keep L4A temporary product files for the orchestrator jobs', 'int', true, 8);
INSERT INTO config_metadata VALUES ('executor.processor.l4b.keep_job_folders', 'Keep L4B temporary product files for the orchestrator jobs', 'int', true, 8);
