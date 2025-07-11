class CropRecommenderApp(tk.Tk):
    def predict_crop(self):
        input_data = [float(self.entries[feature].get()) for feature in X.columns]
        pred_label = le.inverse_transform(clf.predict([input_data]))[0]